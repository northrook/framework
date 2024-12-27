<?php

// -------------------------------------------------------------------
// config\view\components
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Symfony\DependencyInjection\CompilerPass;
use Core\View\ComponentFactory;
use Core\View\ComponentFactory\ComponentBag;

return static function( ContainerConfigurator $container ) : void {
    $container->services()

            // Component Service Locator
        ->set( 'view.component_locator' )
        ->tag( 'container.service_locator' )
        ->args( CompilerPass::PLACEHOLDER_ARGS )

            // The Factory
        ->set( ComponentFactory::class )
        ->arg( '$locator', service( 'view.component_locator' ) )
        ->arg( '$components', abstract_arg( ComponentBag::class ) )
        ->arg( '$tags', abstract_arg( 'ComponentProperties::tagged' ) )
        ->arg( '$logger', service( 'logger' ) )
        ->tag( 'core.service_locator' )
        ->tag( 'monolog.logger', ['channel' => 'components'] )
        ->autowire()
        ->private(); // ->lazy()
};
