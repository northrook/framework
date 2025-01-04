<?php

declare(strict_types=1);

namespace Core;

use Core\Framework\Controller;
use Core\Framework\Controller\Template;
use Core\Symfony\DependencyInjection\Autodiscover;
use Core\Symfony\Interface\ServiceContainerInterface;
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
    // ..Constructor
    // - documentView
    // - cache  - append only on-disk no expiry, clear on exception
    // - logger - for logging
    // Clerk using Facade

    /** @var class-string|false The `Controller` used. */
    protected string|false $controller;

    /** @var false|string The `Controller::method` called. */
    protected string|false $action;

    /** @var string the current `_route` name */
    protected string $route;

    public function __construct(
        protected readonly DocumentView    $documentView, // lazy
        // config\framework\http
        #[Autowire( service : 'cache.core.http_event' )]
        protected readonly CacheInterface  $cache,
        // #[Autowire( service : 'logger' )] // autodiscover
        protected readonly LoggerInterface $logger,
    ) {
        dump( \spl_object_id( $this ).'\\'.__METHOD__, $this );
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST  => 'onKernelRequest',
            KernelEvents::VIEW     => 'onKernelView',
            KernelEvents::RESPONSE => [
                ['earlyKernelResponse', 512],
                ['onKernelResponse', 32],
            ],
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

        dump( \spl_object_id( $this ).'\\'.__METHOD__, $this, $event );

        $htmx = $event->getRequest()->headers->has( 'hx-request' );

        $event->getRequest()->attributes->set( 'hx-request', $htmx );
        $event->getRequest()->attributes->set( 'http-type', $htmx ? 'XMLHttpRequest' : 'HttpRequest' );
        $event->getRequest()->attributes->set( 'view-type', $htmx ? Template::CONTENT : Template::DOCUMENT );

        $controllerTemplate = Reflect::getAttribute( $this->controller, Template::class );
        $methodTemplate     = Reflect::getAttribute( [$this->controller, $this->action], Template::class );

        $event->getRequest()->attributes->add(
            [
                'templates' => [
                    '_document_template' => $controllerTemplate?->name,
                    '_content_template'  => $methodTemplate?->name,
                ],
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

        dump( \spl_object_id( $this ).'\\'.__METHOD__, $this, $event );

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

    public function earlyKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        dump( \spl_object_id( $this ).'\\'.__METHOD__.'@512', $this, $event );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        dump( \spl_object_id( $this ).'\\'.__METHOD__.'@32', $this, $event );
    }

    public function onKernelException( ExceptionEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        dump( \spl_object_id( $this ).'\\'.__METHOD__, $this, $event );
    }

    private function ignoredEvent( KernelEvent $event ) : bool
    {
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
            [$this->controller, $this->action] = $this->cache->get(
                \str_replace( [':', '-', '@', '&'], '.', $this->route ).'.http_event',
                fn() => $this->resolveEventController( $event ),
            );
        }
        catch ( \Psr\Cache\InvalidArgumentException $e ) {
            $this->logger->alert( $e->getMessage() );
        }

        return false;
    }

    /**
     * @param KernelEvent $event
     *
     * @return array{class-string|false,false|string}
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
            return [false, false];
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
            return [false, false];
        }

        // Bail if required Interface isn't implemented
        if ( ! \is_subclass_of( $controller, ServiceContainerInterface::class ) ) {
            return [false, false];
        }

        return [$controller, $method];
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
