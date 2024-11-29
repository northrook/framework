<?php

// -------------------------------------------------------------------
// config\framework\settings
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\CoreBundle;
use Core\Framework\{Pathfinder, Settings};

return static function( ContainerConfigurator $container ) : void {
    foreach ( CoreBundle::PARAMETERS as $name => $value ) {
        if ( \is_array( $value ) ) {
            \assert(
                // @phpstan-ignore-next-line | asserts are here to _assert_, we cannot assume type safety
                \is_string( $value[0] ) && \is_int( $value[1] ),
                CoreBundle::class.'::PARAMETERS only accepts strings, or an array of [__DIR__, LEVEL]',
            );
            $value = \dirname( $value[0], $value[1] );
        }
        $container->parameters()->set( $name, Pathfinder::normalize( $value ) );
    }

    $container->services()
            // Settings handler
        ->set( Settings::class )
        ->args( [param( 'path.settings_store' )] )
        ->tag( 'controller.service_arguments' )
        ->autowire();
};
