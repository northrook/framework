<?php

declare(strict_types=1);

namespace Core\HTTP;

use Core\Framework\Response\{Document, Headers, Parameters};
use Core\Symfony\EventListener\HttpEventListener;
use Core\View\Template\TemplateCompiler;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, ResponseEvent};
use JetBrains\PhpStorm\NoReturn;
use Northrook\Logger\Log;
use Symfony\Component\HttpKernel\Exception\{NotFoundHttpException};
use Symfony\Component\HttpKernel\KernelEvents;

final class ResponseListener extends HttpEventListener
{
    /** @var 'content'|'document'|'string'|'template' */
    private string $type;

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
            $this->headers()->set( 'X-Robots-Tag', 'noindex, nofollow' );
        }

        if ( 'document' === $this->type ) {
            dump( __METHOD__.'[document]' );
            // $view = new HtmlViewDocument(
            //         $this->document(),
            //         $this->content,
            //         $this->serviceLocator,
            // );

            // $this->content = $view->render();
        }

        // $event->getResponse()->setContent( $this->content );
        // $this->setResponseHeaders( $event );
        dump( $this );
    }

    #[NoReturn]
    public function onKernelException( ExceptionEvent $event ) : void
    {
        dd( $event::class, $event, $this );
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

        $template = \str_ends_with( $this->content, '.latte' )
                ? $this->content
                : $this->controllerTemplate( $event );

        $this->template()->clearTemplateCache();

        $this->content = $this->template()->render( $template, $this->parameters() );
    }

    final protected function setResponseHeaders( ResponseEvent $event ) : void
    {
        // Always remove the identifying header
        // \header_remove( 'X-Powered-By' );

        // Merge headers
        $event->getResponse()->headers->add( $this->headers()->all() );

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

    /**
     * Determine if the {@see Response} `$content` is a template.
     *
     * - Empty `$content` will use {@see Controller} attribute templates.
     * - If the `$content` contains no whitespace, and ends with `.latte`, it is a template
     * - All other strings will be considered as `text/plain`
     *
     * @param ResponseEvent $event
     *
     * @return string
     */
    private function controllerTemplate( ResponseEvent $event ) : string
    {
        $use = $event->getRequest()->attributes->get( 'use_template' );

        /** @var array{_document_template: ?string, _content_template: ?string} $template */
        $templates = $event->getRequest()->attributes->get( 'templates' );

        if ( ! $template = $templates[$use] ?? null ) {
            throw new NotFoundHttpException( 'Template "'.$this->content.'" not found.' );
        }

        $this->type = match ( $use ) {
            '_document_template' => 'document',
            '_content_template'  => 'content',
            default              => 'template',
        };

        return $template;
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

    private function template() : TemplateCompiler
    {
        return $this->serviceLocator( TemplateCompiler::class );
    }
}
