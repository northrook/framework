<?php

namespace Core\View;

use Core\Framework\DependencyInjection\ServiceContainer;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, ResponseEvent};

final class ResponseRenderer
{
    use ServiceContainer;

    protected readonly ?string $htmxRequest;

    protected readonly ?string $requestType;

    protected readonly ?string $contentTemplate;

    protected readonly ?string $documentTemplate;

    public function __construct() {}

    public function __invoke( ResponseEvent|ExceptionEvent $event ) : void
    {
        if ( ! $event->getRequest()->attributes->get( '_request_type' ) ) {
            return;
        }

        $template = 'document' === $this->requestType ? $this->documentTemplate : $this->contentTemplate;

        $content = $this->serviceLocator( TemplateEngine::class )->render( $template );

        dump( $this, $event, $content );
    }
}
