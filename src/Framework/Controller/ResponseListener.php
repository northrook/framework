<?php

namespace Core\Framework\Controller;

use Core\Framework\Controller;
use Core\Symfony\DependencyInjection\ServiceContainer;
use Core\Framework\Response\Document;
use Core\Symfony\EventListener\ResponseEventListener;
use Northrook\Clerk;
use Northrook\Logger\Log;
use Stringable;
use Support\Reflect;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\HttpKernel\Event\{ControllerEvent, ResponseEvent, TerminateEvent, ViewEvent};
use function Support\explode_class_callable;
use ReflectionException;

final class ResponseListener extends ResponseEventListener
{
    use ServiceContainer;

    protected const string
        DOCUMENT = '_document_template',
        CONTENT  = '_content_template';

    /**
     * For debugging - will be cached later.
     *
     * @var array<string, array{
     *      _document_template: ?string,
     *      _content_template: ?string
     *  }>
     */
    private array $responseTemplateCache = [];

    public function onKernelController( ControllerEvent $event ) : void
    {
        Clerk::event( __METHOD__, $this::class );
        if ( ! $this->handleController( $event->getRequest() ) ) {
            return;
        }

        $isHtmx = $event->getRequest()->headers->has( 'hx-request' );

        $event->getRequest()->attributes->set( '_htmx_request', $isHtmx );
        $event->getRequest()->attributes->set( '_view_template', $isHtmx ? $this::CONTENT : $this::DOCUMENT );
    }

    public function onKernelView( ViewEvent $event ) : void
    {
        if ( ! $this->handleController( $event->getRequest() ) ) {
            return;
        }

        Clerk::event( __METHOD__, $this::class );

        $controller = $event->controllerArgumentsEvent->getController();

        if ( \is_array( $controller ) && $controller[0] instanceof Controller ) {
            $controller = $controller[0];
            try {
                Reflect::class( $controller )
                    ->getMethod( 'controllerResponseMethods' )
                    ->invoke( $controller );
            }
            catch ( ReflectionException $e ) {
                Log::exception( $e );
            }
        }

        $event->setResponse( $this->resolveViewResponse( $event->getControllerResult() ) );
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( ! $this->handleController( $event->getRequest() ) ) {
            return;
        }

        $event->getRequest()->attributes->add(
            $this->getTemplateAttributes( $event->getRequest() ),
        );
    }

    public function onKernelTerminate( TerminateEvent $event ) : void
    {
        Clerk::event( __METHOD__, $this::class );
        if ( ! $this->handleController( $event->getRequest() ) ) {
            return;
        }
        Log::debug( $this::class.'::'.$event::class );
    }

    /**
     * Determine if the {@see Response} `$content` is a template.
     *
     * - Empty `$content` will use {@see Controller} attribute templates.
     * - If the `$content` contains no whitespace, and ends with `.latte`, it is a template
     * - All other strings will be considered as `text/plain`
     *
     * @param Request $request
     *
     * @return array{_document_template: ?string, _content_template: ?string}
     */
    private function getTemplateAttributes( Request $request ) : array
    {
        Clerk::event( __METHOD__, $this::class );
        $caller = $request->attributes->get( '_controller' );

        \assert( \is_string( $caller ) );

        if ( isset( $this->responseTemplateCache[$caller] ) ) {
            return $this->responseTemplateCache[$caller];
        }

        [$controller, $method] = explode_class_callable( $caller, true );

        $controllerTemplate = Reflect::getAttribute( $controller, Template::class );
        $methodTemplate     = Reflect::getAttribute( [$controller, $method], Template::class );

        return [
            '_document_template' => $controllerTemplate?->name,
            '_content_template'  => $methodTemplate?->name,
        ];
    }

    private function resolveViewResponse( mixed $content ) : Response
    {
        Clerk::event( __METHOD__, $this::class );
        if ( \is_string( $content ) || $content instanceof Stringable ) {
            $content = (string) $content;
        }

        if ( ! ( \is_string( $content ) || \is_null( $content ) ) ) {
            Log::exception(
                exception : new InvalidArgumentException(),
                message   : 'Controller return value is {type}, the {Response} object requires {string}|{null}. {null} was provided instead.',
                context   : ['type' => \gettype( $content )],
            );
            Clerk::event( __METHOD__.'::EXCEPTION', $this::class );
            $content = null;
        }

        return new Response( $content ?: null );
    }
}
