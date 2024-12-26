<?php

// -------------------------------------------------------------------
// config\view\components
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\ComponentFactory;

return static function( ContainerConfigurator $container ) : void {
    $container->services()

            // Component Service Locator
        ->set( 'view.component_locator' )
        ->tag( 'container.service_locator' )
        ->args( [[]] )

            // The Factory
        ->set( ComponentFactory::class )
        ->arg( '$locator', service( 'view.component_locator' ) )
        ->tag( 'core.service_locator' )
        ->tag( 'monolog.logger', ['channel' => 'components'] );
};
