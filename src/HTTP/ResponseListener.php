<?php

declare(strict_types=1);

namespace Core\HTTP;

use Core\Symfony\EventListener\HttpEventListener;

final class ResponseListener extends HttpEventListener
{
    public static function getSubscribedEvents() : array
    {
        return [
            // 'kernel.request'              => 'onKernelRequest',
            // 'kernel.controller'           => 'onKernelController',
            // 'kernel.controller_arguments' => 'onKernelControllerArguments',
            // 'kernel.view'                 => 'onKernelView',
            // 'kernel.response'             => 'onKernelResponse',
            // 'kernel.finish_request'       => 'onKernelFinishRequest',
            // 'kernel.exception'            => 'onKernelException',
            // 'kernel.terminate'            => 'onKernelTerminate',
        ];
    }
}
