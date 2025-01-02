<?php

declare(strict_types=1);

namespace Core\Http;

use Core\TemplateEngine;
use Core\Http\Response\{Document, Headers};
use Core\Service\ToastService;
use Core\View\Component\Toast;
use Core\View\{ComponentFactory, DocumentView, Parameters};
use Core\Symfony\EventListener\HttpEventListener;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, ResponseEvent};
use JetBrains\PhpStorm\NoReturn;
use Northrook\Logger\Log;
use Symfony\Component\HttpKernel\Exception\{NotFoundHttpException};
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ResponseListener extends HttpEventListener
{
    /** @var 'content'|'document'|'string'|'template' */
    private string $type = 'document';

    private string $content;

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::RESPONSE  => ['onKernelResponse', 20],
            KernelEvents::EXCEPTION => ['onKernelException', 40],
        ];
    }

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( $this->shouldSkip( $event ) ) {
            return;
        }

        $this->setResponseContent( $event );

        if ( ! $this->document()->isPublic ) {
            $this->document()->set( 'robots', 'noindex, nofollow' );
            $event->getResponse()->headers->set( 'X-Robots-Tag', 'noindex, nofollow' );
            // $this->headers()->set( );
        }

        $document = $this->serviceLocator( DocumentView::class );

        $document->setInnerContent(
            $this->resolveToastMessages(),
            $this->content,
        );
        // ->enqueueInvokedAssets();

        // if ( 'document' === $this->type ) {
        //     dump( __METHOD__.'[document]' );
        //     $view = new HtmlViewDocument(
        //         $this->document(),
        //         $this->content,
        //         $this->serviceLocator,
        //     );
        //
        //     $this->content = $view->render();
        // }

        if ( 'document' === $this->type ) {
            $document
                ->meta( 'meta.viewport' )
                ->meta( 'document' )
                ->meta( 'robots' )
                ->meta( 'meta' )
                ->assets();
            // ->assets( 'font' )
            // ->assets( 'script' )
            // ->assets( 'style' )
            // ->assets( 'link' );

            $this->content = $document->renderDocumentHtml();
        }
        else {
            $document
                ->meta( 'document' )
                ->meta( 'meta' );
            // ->assets( 'font' )
            // ->assets( 'script' )
            // ->assets( 'style' )
            // ->assets( 'link' );
            $this->content = $document->renderContentHtml();
        }

        $event->getResponse()->setContent( $this->content );
        $this->setResponseHeaders( $event );
        // dump( $this );

        $this->clerk::stop( $this->listenerId );
    }

    #[NoReturn]
    public function onKernelException( ExceptionEvent $event ) : void
    {
        // dd( $event::class, $event, $this );
    }

    final protected function resolveToastMessages( ?FlashBagInterface $flashBag = null ) : array
    {
        $toastService = $this->serviceLocator( ToastService::class );

        // Bail early if no Toasts are found
        if ( ! $toastService->hasMessages() ) {
            return [];
        }

        $toasts = [];

        foreach ( $toastService->getAllMessages() as $message ) {
            // $component = $factory->getComponent( Toast::class );
            // $component->create( $message->getArguments() );

            // dump( $component->render(  ) );
            $toasts[] = $this->componentFactory()->render( 'view.component.toast', $message->getArguments() );
            // $toasts[] = $factory->create( $message );
        }
        // foreach ( $this->serviceLocator( ToastService::class )->getMessages() as $id => $message ) {
        //     $this->notifications[$id] = new Notification(
        //             $message->type,
        //             $message->title,
        //             $message->description,
        //             $message->timeout,
        //     );
        //
        //     if ( ! $message->description ) {
        //         $this->notifications[$id]->attributes->add( 'class', 'compact' );
        //     }
        //
        //     if ( ! $message->timeout && 'error' !== $message->type ) {
        //         $this->notifications[$id]->setTimeout( 5_000 );
        //     }
        //
        //     $this->notifications[$id] = (string) $this->notifications[$id];
        // }

        // dump( $toasts );

        return $toasts;
    }

    final protected function setResponseContent( ResponseEvent $event ) : void
    {
        if ( isset( $this->content ) ) {
            Log::warning(
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

        $this->template()->clearTemplateCache();

        $this->content = $this->template()->render( $template, $this->parameters() );
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

        if ( $this->document()->isPublic ) {
            $event->getResponse()->headers->set( 'X-Robots-Tag', 'noindex, nofollow' );
        }

        // TODO : X-Robots
        // TODO : lang
        // TODO : cache
    }

    private function document() : Document
    {
        return $this->serviceLocator( Document::class );
    }

    protected function headers() : Headers
    {
        return $this->serviceLocator( Headers::class );
    }

    private function parameters() : object|array
    {
        return $this->serviceLocator( Parameters::class )->getParameters();
    }

    private function template() : TemplateEngine
    {
        return $this->serviceLocator( TemplateEngine::class );
    }

    private function componentFactory() : ComponentFactory
    {
        return $this->serviceLocator( ComponentFactory::class );
    }
}
