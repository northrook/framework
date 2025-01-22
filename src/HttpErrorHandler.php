<?php

declare(strict_types=1);

namespace Core;

use Core\Framework\Controller\Template;
use Core\Http\ErrorResponse;
use Core\Assets\AssetManager;
use Core\Service\ToastService;
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
    /** @var string */
    private string $type = 'document';

    private readonly Document $document;

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
        $this->type = (string) $event->getRequest()->attributes->get( 'view-type', Template::DOCUMENT );

        $content  = $this->templateEngine->render( 'error/404.latte' );
        $document = new DocumentView( $this->document );
        $document->setInnerHtml( $content );
        $event->setResponse( new ErrorResponse( $document->renderDocument() ) );
        // dump( \spl_object_id( $this ).'\\'.__METHOD__, $this, $event, $content );
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
