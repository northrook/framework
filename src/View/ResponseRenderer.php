<?php

namespace Core\View;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class ResponseRenderer
{
    public function __construct( private readonly TemplateEngine $templateEngine ) {}

    public function __invoke( ResponseEvent $event ) : void
    {
        if ( ! $event->getRequest()->attributes->get( '_request_type' ) ) {
            return;
        }

        [$htmx_request, $request_type, $content_template, $document_template] = $this->getAttributes( $event );

        dump( $this, $htmx_request, $request_type, $content_template, $document_template );

        if ( $content_template ) {
            dump(
                $this->templateEngine->render( $content_template, [] ),
            );
        }
    }

    private function getAttributes( ResponseEvent $event ) : array
    {
        $attributes = \array_intersect_key(
            [
                '_htmx_request'      => null,
                '_request_type'      => null,
                '_content_template'  => null,
                '_document_template' => null,
            ],
            $event->getRequest()->attributes->all(),
        );

        dump( $attributes );
        return $attributes;
    }
}
