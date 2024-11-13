<?php

namespace Core\View;

use Core\Framework\DependencyInjection\ServiceContainer;
use Core\Framework\Response\Parameters;
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

        $this->template()->clearTemplateCache();

        $template   = $event->getRequest()->attributes->get( $viewTemplate );
        $parameters = $this->serviceLocator( Parameters::class );

        $content = $this->template()->render( $template, $parameters );

        dump( $this, $event, $parameters, $content );
    }

    private function template() : TemplateEngine
    {
        return $this->serviceLocator( TemplateEngine::class );
    }
}
