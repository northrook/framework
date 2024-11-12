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
        $viewTemplate = $event->getRequest()->attributes->get( '_view_template' );

        if ( ! \is_string( $viewTemplate ) ) {
            return;
        }

        $template = $event->getRequest()->attributes->get( $viewTemplate );

        $content = $this->serviceLocator( TemplateEngine::class )->render( $template );

        dump( $this, $event, $content );
    }
}
