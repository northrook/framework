<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Symfony\DependencyInjection\Autodiscover;
use Core\Symfony\Interface\ServiceContainerInterface;
use Core\View\Template\DocumentView;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, KernelEvent, RequestEvent, ResponseEvent, ViewEvent};
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Cache\CacheInterface;
use function Support\explode_class_callable;
use InvalidArgumentException;

#[Autodiscover( tag : ['monolog.logger', ['channel' => 'http_event']] )]
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
        protected readonly DocumentView    $documentView,
        // config\framework\http
        #[Autowire( service : 'cache.core.http_event' )]
        protected readonly CacheInterface  $cache,
        // #[Autowire( service : 'logger' )] // autodiscover
        protected readonly LoggerInterface $logger,
    ) {}

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

        dump( __METHOD__ );
    }

    public function onKernelView( ViewEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        dump( __METHOD__ );
    }

    public function earlyKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        dump( __METHOD__.'@512' );
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        dump( __METHOD__.'@32' );
    }

    public function onKernelException( ExceptionEvent $event ) : void
    {
        if ( $this->ignoredEvent( $event ) ) {
            return;
        }

        dump( __METHOD__ );
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

        if ( \is_subclass_of( $controller, ServiceContainerInterface::class ) ) {
            return [$controller, $method];
        }

        return [false, false];
    }
}
