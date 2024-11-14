<?php

// -------------------------------------------------------------------
// config\view\components
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\ComponentFactory;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( ComponentFactory::class )
        ->args(
            [
                /** Replaced by {@see \Core\View\Compiler\RegisterCoreComponentsPass} */
                [], // $components
                [], // $tags
                service( 'logger' )->nullOnInvalid(),
            ],
        )
        ->tag( 'core.service_locator' )
        ->tag( 'monolog.logger', ['channel' => 'components'] );
};
