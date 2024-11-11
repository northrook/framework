<?php

// -------------------------------------------------------------------
// config\framework\settings
// -------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Settings;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function( ContainerConfigurator $container ) : void
{
    $container->services()
            // Settings handler
              ->set( Settings::class )
              ->args( [ '%kernel.cache_dir%/framework-settings.php' ] )
              ->tag( 'controller.service_arguments' )
              ->autowire();
};
