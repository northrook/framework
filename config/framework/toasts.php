<?php

// -------------------------------------------------------------------
// config\framework\toasts
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Autowire\Toast;
use Core\Service\ToastService;

return static function( ContainerConfigurator $container ) : void {
    $container->services()

            // Toast Flashbag Handler
        ->set( ToastService::class )
        ->args( [service( 'request_stack' )] )
        ->tag( 'core.service_locator' )

            // Toast Action
        ->set( Toast::class )
        ->args( [service( ToastService::class )] );
};
