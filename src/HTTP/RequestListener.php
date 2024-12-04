<?php

declare(strict_types=1);

namespace Core\HTTP;

use Core\Framework\Controller;
use Core\Framework\Controller\Template;
use Core\Symfony\EventListener\HttpEventListener;
use Symfony\Component\HttpKernel\Event\{ControllerArgumentsEvent,
    ControllerEvent,
    ExceptionEvent,
    RequestEvent,
    ResponseEvent,
    ViewEvent
};
use Northrook\Clerk;
use Northrook\Logger\Log;
use Support\Reflect;
use ReflectionException;
use Symfony\Component\HttpFoundation\{Request, Response};
use InvalidArgumentException;
use Stringable;
use Symfony\Component\HttpKernel\KernelEvents;
use function Support\explode_class_callable;

final class RequestListener extends HttpEventListener
{
    protected const string
        DOCUMENT = '_document_template',
        CONTENT  = '_content_template';

    /**
     * For debugging - will be cached later.
     *
     * @var array<string, array<string, array{templates: array{_document_template: ?string, _content_template: ?string}}>
     */
    private array $responseTemplateCache = [];

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST         => 'onKernelRequest',
            KernelEvents::CONTROLLER      => 'onKernelController',
            'kernel.controller_arguments' => 'onKernelControllerArguments',
            KernelEvents::VIEW            => ['onKernelView'],
            KernelEvents::RESPONSE        => ['onKernelResponse', 512],
            'kernel.exception'            => 'onKernelException',
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
        if ( $this->shouldSkip( $event ) ) {
            return;
        }
        Clerk::event( __METHOD__, $this::class );

        $xhr = $event->getRequest()->headers->has( 'hx-request' );

        $event->getRequest()->attributes->set( 'htmx', $xhr );
        $event->getRequest()->attributes->set( 'type', $xhr ? 'XMLHttpRequest' : 'HttpRequest' );
        $event->getRequest()->attributes->set( 'use_template', $xhr ? $this::CONTENT : $this::DOCUMENT );

        dump( __METHOD__ );
    }

    public function onKernelController( ControllerEvent $event ) : void
    {
        if ( $this->shouldSkip( $event ) ) {
            return;
        }
        dump( __METHOD__ );
    }

    public function onKernelControllerArguments( ControllerArgumentsEvent $event ) : void
    {
        if ( $this->shouldSkip( $event ) ) {
            return;
        }
        dump( __METHOD__ );
    }

    public function onKernelView( ViewEvent $event ) : void
    {
        if ( $this->shouldSkip( $event ) ) {
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
        dump( $this );
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->shouldSkip( $event ) ) {
            return;
        }

        $event->getRequest()->attributes->add(
            $this->getTemplateAttributes( $event->getRequest() ),
        );
    }

    public function onKernelException( ExceptionEvent $event ) : void
    {
        if ( $this->shouldSkip( $event ) ) {
            return;
        }

        dump( __METHOD__ );
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

    /**
     * Determine if the {@see Response} `$content` is a template.
     *
     * - Empty `$content` will use {@see Controller} attribute templates.
     * - If the `$content` contains no whitespace, and ends with `.latte`, it is a template
     * - All other strings will be considered as `text/plain`
     *
     * @param Request $request
     *
     * @return array{templates: array{_document_template: ?string, _content_template: ?string}}
     */
    private function getTemplateAttributes( Request $request ) : array
    {
        Clerk::event( __METHOD__, $this::class );
        $caller = $request->attributes->get( '_controller' );

        \assert( \is_string( $caller ) );

        return $this->responseTemplateCache[$caller] ??= ( function() use ( $caller ) : array {
            [$controller, $method] = explode_class_callable( $caller, true );

            $controllerTemplate = Reflect::getAttribute( $controller, Template::class );
            $methodTemplate     = Reflect::getAttribute( [$controller, $method], Template::class );

            return [
                'templates' => [
                    '_document_template' => $controllerTemplate?->name,
                    '_content_template'  => $methodTemplate?->name,
                ],
            ];
        } )();
    }
}
