<?php

declare(strict_types=1);

namespace Core;

use Core\Framework\Controller;
use Core\Framework\Controller\Template;
use Core\Service\{AssetManager, ToastService};
use Core\Symfony\DependencyInjection\Autodiscover;
use Core\View\{ComponentFactory, Document, TemplateEngine};
use Core\View\Template\DocumentView;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent,
    ResponseEvent
};
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Cache\CacheInterface;

#[Autodiscover(
    tag      : ['monolog.logger' => ['channel' => 'http_event']],
    autowire : true,
)]
final class HttpErrorHandler implements EventSubscriberInterface
{
    /** @var string the current `_route` name */
    protected string $route;

    /** @var class-string<Controller>|false The `Controller` used. */
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
        protected readonly DocumentView     $documentView,
        #[Autowire( service : TemplateEngine::class )]
        protected readonly TemplateEngine   $templateEngine,
        protected readonly ComponentFactory $componentFactory,
        protected readonly AssetManager     $assetManager,
        protected readonly ToastService     $toastService,
        // config\framework\http
        #[Autowire( service : 'cache.core.http_event' )]
        protected readonly CacheInterface   $cache,
        // #[Autowire( service : 'logger' )] // autodiscover
        protected readonly LoggerInterface  $logger,
    ) {
        $this->document = $this->documentView->document;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException( ExceptionEvent $event ) : void
    {
        $content = $this->templateEngine->render( 'error/404.latte' );
        // $event->

        dump( \spl_object_id( $this ).'\\'.__METHOD__, $this, $event, $content );
    }

    final protected function handleToastMessages() : void
    {
        if ( ! $this->toastService->hasMessages() ) {
            return;
        }

        $toasts = [];

        foreach ( $this->toastService->getAllMessages() as $message ) {
            $toasts[] = $this->componentFactory->render(
                'view.component.toast',
                $message->getArguments(),
            );
        }

        $this->documentView->body->content( $toasts, true );
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
}
