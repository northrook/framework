<?php

// -------------------------------------------------------------------
// config\framework\response
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\HTTP\Response\{Document, Headers};
use Core\View\Parameters;

return static function( ContainerConfigurator $container ) : void {
    $container->services()->defaults()
        ->tag( 'controller.service_arguments' )
        ->autowire()

            // ResponseHeaderBag Service
        ->set( Headers::class )
        ->arg( 0, service( 'request_stack' ) )

            // Document Properties
        ->set( Document::class )

            // Template Parameters
        ->set( Parameters::class );
};
