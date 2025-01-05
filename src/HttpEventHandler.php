<?php

declare(strict_types=1);

namespace Core;

use Core\Framework\Controller;
use Core\Framework\Controller\Template;
use Core\Symfony\DependencyInjection\Autodiscover;
use Core\Symfony\Interface\ServiceContainerInterface;
use Core\View\{Document, TemplateEngine};
use Core\View\Template\DocumentView;
use Northrook\Clerk;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Stringable;
use Support\Reflect;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, KernelEvent, RequestEvent, ResponseEvent, ViewEvent};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Cache\CacheInterface;
use function Support\explode_class_callable;
use InvalidArgumentException;

#[Autodiscover(
    tag      : ['monolog.logger' => ['channel' => 'http_event']],
    autowire : true,
)]
final class HttpEventHandler implements EventSubscriberInterface
{
    /** @var string the current `_route` name */
    protected string $route;

    /** @var class-string|false The `Controller` used. */
    protected string|false $controller;

    /** @var false|string The `Controller::method` called. */
    protected string|false $action;

    protected string|false $documentTemplate;

    protected string|false $contentTemplate;

    private readonly bool $ignoredEvent;

    /** @var 'content'|'document'|'string'|'template' */
    private string $type = 'document';

    private readonly Document $document;

    private string $content;

    public function __construct(
        protected readonly DocumentView    $documentView, // lazy
        #[Autowire( service : TemplateEngine::class )]
        protected readonly TemplateEngine  $templateEngine,
        // config\framework\http
        #[Autowire( service : 'cache.core.http_event' )]
        protected readonly CacheInterface  $cache,
        // #[Autowire( service : 'logger' )] // autodiscover
        protected readonly LoggerInterface $logger,
    ) {
        $this->document = $this->documentView->document;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST   => 'onKernelRequest',
            KernelEvents::VIEW      => 'onKernelView',
            KernelEvents::RESPONSE  => ['onKernelResponse', 32],
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * Parse the incoming {@see RequestEvent}:
     * - Determine type: `xhr` for client fetch request, otherwise `http`.
     *
     * @param RequestEvent $event
     *
     * @return void
     */
    public function onKernelRequest( RequestEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        Clerk::event( __METHOD__, $this::class );

        $htmx = $event->getRequest()->headers->has( 'hx-request' );

        $event->getRequest()->attributes->set( 'hx-request', $htmx );
        $event->getRequest()->attributes->set( 'http-type', $htmx ? 'XMLHttpRequest' : 'HttpRequest' );
        $event->getRequest()->attributes->set( 'view-type', $htmx ? Template::CONTENT : Template::DOCUMENT );
        $event->getRequest()->attributes->add(
            [
                '_view_document' => $this->documentTemplate,
                '_view_template' => $this->contentTemplate,
            ],
        );

        Clerk::stop( __METHOD__ );
    }

    public function onKernelView( ViewEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        Clerk::event( __METHOD__, $this::class );

        if ( $controller = $event->controllerArgumentsEvent?->getController() ) {
            /**
             * Call methods annotated with {@see OnContent::class} or {@see OnDocument::class}.
             */
            if ( \is_array( $controller ) && $controller[0] instanceof Controller ) {
                $controller = $controller[0];
                try {
                    ( new ReflectionClass( $controller ) )
                        ->getMethod( 'controllerResponseMethods' )
                        ->invoke( $controller );
                }
                catch ( ReflectionException $exception ) {
                    $this->logger->error( $exception->getMessage(), ['exception' => $exception] );
                }
            }
        }

        $event->setResponse( $this->resolveViewEventResponse( $event->getControllerResult() ) );

        Clerk::stop( __METHOD__ );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        Clerk::event( __METHOD__, $this::class );

        $this->setResponseContent( $event );

        $this->documentView->content( $this->content );

        // $this->content = $document->render();

        $event->getResponse()->setContent( $this->documentView->render() );

        dump( \spl_object_id( $this ).'\\'.__METHOD__.'@32', $this, $event );
        Clerk::stop( __METHOD__ );
    }

    public function onKernelException( ExceptionEvent $event ) : void
    {
        dump( \spl_object_id( $this ).'\\'.__METHOD__, $this, $event );
    }

    // .. Response

    final protected function setResponseContent( ResponseEvent $event ) : void
    {
        if ( isset( $this->content ) ) {
            $this->logger->warning(
                '{method} called repeatedly, but will only be handled {once}.',
                ['method' => __METHOD__],
            );
            return;
        }

        $this->content = (string) $event->getResponse()->getContent() ?: '';

        // If $content any whitespace, we can safely assume it not a template string
        if ( \str_contains( $this->content, ' ' ) ) {
            $this->type = 'string';
            return;
        }

        /** @var string $use */
        $use = $event->getRequest()->attributes->get( 'use_template' );

        /** @var array{_document_template: ?string, _content_template: ?string} $templates */
        $templates = $event->getRequest()->attributes->get( 'templates' );

        if ( \str_ends_with( $this->content, '.latte' ) ) {
            $template = $this->content;

            $this->type = $use ? 'document' : 'template';
        }
        else {
            if ( ! $template = $templates[$use] ?? null ) {
                throw new NotFoundHttpException( 'Template "'.$this->content.'" not found.' );
            }

            $this->type = match ( $use ) {
                '_document_template' => 'document',
                '_content_template'  => 'content',
                default              => 'template',
            };
        }

        $this->templateEngine->clearTemplateCache();

        $this->content = $this->templateEngine->render( $template );
    }

    final protected function setResponseHeaders( ResponseEvent $event ) : void
    {
        // Always remove the identifying header
        // \header_remove( 'X-Powered-By' );

        $event->getResponse()->headers->set( 'Content-Type', 'text/html', false );

        if ( 'content' === $this->type ) {
            return;
        }

        // Document only headers

        if ( $this->document->isPublic ) {
            $event->getResponse()->headers->set( 'X-Robots-Tag', 'noindex, nofollow' );
        }

        // TODO : X-Robots
        // TODO : lang
        // TODO : cache
    }

    // :: Response

    private function ignoredEvent( KernelEvent $event ) : bool
    {
        if ( isset( $this->ignoredEvent ) ) {
            return $this->ignoredEvent;
        }

        // Ignore raised Exceptions
        if ( $event instanceof ExceptionEvent ) {
            $this->logger->info(
                'Skipped event {event}.',
                ['event' => $event],
            );
            return true;
        }

        // Only parse GET requests
        if ( ! $event->getRequest()->isMethod( 'GET' ) ) {
            return true;
        }

        // Retrieve the _route attribute
        $this->route = (string) $event->getRequest()->attributes->get( '_route', '' );

        if ( ! $this->route ) {
            $this->logger->alert(
                'Expected a {_route} parameter, but none was found.',
                ['event' => $event, 'requestAttributes' => $event->getRequest()->attributes->all()],
            );
            return true;
        }

        // [$this->controller, $this->action] = $this->resolveEventController( $event );
        try {
            [$this->controller, $this->action, $this->contentTemplate, $this->documentTemplate] = $this->cache->get(
                \str_replace( [':', '-', '@', '&'], '.', $this->route ).'.http_event',
                fn() => $this->resolveEventController( $event ),
            );
        }
        catch ( \Psr\Cache\InvalidArgumentException $e ) {
            $this->logger->alert( $e->getMessage() );
        }

        return $this->ignoredEvent = ! $this->controller;
    }

    /**
     * @param KernelEvent $event
     *
     * @return array{class-string|false,false|string,false|string,false|string}
     */
    private function resolveEventController( KernelEvent $event ) : array
    {
        // Get the _controller attribute from the Request object
        $controller = $event->getRequest()->attributes->get( '_controller' );

        // We can safely skip early if the `_controller` is anything but a string
        if ( ! $controller || ! \is_string( $controller ) ) {
            $this->logger->warning(
                '{method}: Controller attribute was expected be a string. Returning {false}.',
                ['method' => __METHOD__],
            );
            return [false, false, false, false];
        }

        // Resolve the `$controller` to a class-string and ensure it exists
        try {
            [$controller, $method] = explode_class_callable( $controller, true );
        }
        catch ( InvalidArgumentException $exception ) {
            $this->logger->error(
                $exception->getMessage(),
                ['exception' => $exception],
            );
            return [false, false, false, false];
        }

        // Bail if required Interface isn't implemented
        if ( ! \is_subclass_of( $controller, ServiceContainerInterface::class ) ) {
            return [false, false, false, false];
        }

        $controllerTemplate = Reflect::getAttribute( $controller, Template::class );
        $methodTemplate     = Reflect::getAttribute( [$controller, $method], Template::class );

        return [$controller, $method, $controllerTemplate->name ?? false, $methodTemplate->name ?? false];
    }

    private function resolveViewEventResponse( mixed $content ) : Response
    {
        if ( \is_string( $content ) || $content instanceof Stringable ) {
            $content = (string) $content;
        }

        if ( ! ( \is_string( $content ) || \is_null( $content ) ) ) {
            $this->logger->error(
                message : 'Controller {controller} return value is {type}; {required}, {provided} provided as fallback.',
                context : [
                    'controller' => $this->controller,
                    'type'       => \gettype( $content ),
                    'required'   => 'string|null',
                    'provided'   => 'null',
                ],
            );
            $content = null;
        }

        return new Response( $content ?: null );
    }
}
