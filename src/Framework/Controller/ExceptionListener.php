<?php

namespace Core\Framework\Controller;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ExceptionListener
{
    public function __invoke( ExceptionEvent $event ) : void
    {
        dd( $event );
    }
}
