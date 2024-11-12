<?php

namespace Core\View;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class ResponseRenderer
{
    public function __construct( private readonly TemplateEngine $templateEngine ) {}

    public function __invoke( ResponseEvent $event ) : void
    {
        dump( $event, $this );
    }
}
