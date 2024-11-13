<?php

namespace Core\View;

use Core\Framework\Controller;
use Core\Framework\DependencyInjection\ServiceContainer;
use Core\Framework\Response\{Document, Headers, Parameters};
use Core\Symfony\EventListener\ResponseEventListener;
use Core\View\Render\ViewDocument;
use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, ResponseEvent};

final class ResponseRenderer extends ResponseEventListener
{
    use ServiceContainer;

    public const string
        DOCUMENT = 'document',
        CONTENT  = 'content',
        PLAIN    = 'plain';

    #[ExpectedValues( valuesFromClass : self::class )]
    protected string $type;

    protected string $content;

    public function onKernelResponse( ResponseEvent $event ) : void
    {
        if ( ! $this->handleController( $event->getRequest() ) ) {
            return;
        }

        $this->content = $this->handleContent( $event );

        if ( ! $this->document()->isPublic ) {
            $this->document()->set( 'robots', 'noindex, nofollow' );
            $this->headers()->set( 'X-Robots-Tag', 'noindex, nofollow' );
        }

        if ( $this->type === $this::DOCUMENT ) {
            $view = new ViewDocument(
                $this->document(),
                $this->content,
                $this->serviceLocator,
            );

            $this->content = $view->render();
        }

        $event->getResponse()->setContent( $this->content );
        $this->responseHeaders( $event );
    }

    protected function handleContent( ResponseEvent $event ) : string
    {
        if ( isset( $this->content ) ) {
            Log::warning( '{method} called repeatedly, but will only be handled once.', ['method' => __METHOD__] );
            return $this->content;
        }

        $content = (string) $event->getResponse()->getContent() ?: '';

        // Any whitespace and we can safely assume it not a template string
        if ( \str_contains( $content, ' ' ) ) {
            $this->type           = $this::PLAIN;
            return $this->content = $content;
        }

        $isTemplate = \str_ends_with( $content, '.latte' );

        // TODO : Validate template exists

        $template = $isTemplate ? $content : $this->resolveTemplate( $event->getRequest() );

        if ( ! $template ) {
            throw new InvalidArgumentException( 'Unable to resolve template.' );
        }

        $this->template()->clearTemplateCache();

        $parameters = $this->parameters();

        $this->content = $this->template()->render( $template, $this->parameters() );

        return $this->content;
    }

    protected function resolveTemplate( Request $request ) : ?string
    {
        $viewTemplate = $request->attributes->get( '_view_template' );

        $this->type = match ( $viewTemplate ) {
            '_document_template' => $this::DOCUMENT,
            '_content_template'  => $this::CONTENT,
            default              => $this::PLAIN,
        };

        return $request->attributes->get( $viewTemplate );
    }

    public function onKernelException( ExceptionEvent $event ) : void
    {
        dump( $event::class );
    }

    protected function document() : Document
    {
        return $this->serviceLocator( Document::class );
    }

    protected function headers() : Headers
    {
        return $this->serviceLocator( Headers::class );
    }

    private function template() : TemplateEngine
    {
        return $this->serviceLocator( TemplateEngine::class );
    }

    private function parameters() : object|array
    {
        return $this->serviceLocator( Parameters::class )->getParameters();
    }

    private function responseHeaders( ResponseEvent $event ) : void
    {
        // Always remove the identifying header
        // \header_remove( 'X-Powered-By' );

        // Merge headers
        $event->getResponse()->headers->add( $this->headers()->all() );

        $event->getResponse()->headers->set( 'Content-Type', 'text/html', false );

        if ( $this->type === $this::CONTENT ) {
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
     * @param false|string $content
     *
     * @return bool
     */
    private function renderContentTemplate( false|string $content = null ) : bool
    {
        // If the string is empty, use Controller attributes
        if ( ! $content ) {
            return true;
        }

        // Any whitespace and we can safely assume it not a template string
        if ( \str_contains( $content, ' ' ) ) {
            return false;
        }

        return (bool) ( \str_ends_with( $content, '.latte' ) );
    }
}
