<?php

// -------------------------------------------------------------------
// config\view\renderer
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Pathfinder;
use Core\Service\GlobalGetService;
use Core\View\Latte\ViewComponentExtension;
use Core\View\{Parameters, TemplateEngine};

return static function( ContainerConfigurator $container ) : void {
    //
    $container->services()
        ->set( GlobalGetService::class )
        ->autowire()

            //
        ->set( TemplateEngine::class )
        ->tag( 'core.service_locator' )
        ->args(
            [
                param( 'dir.cache.view' ),
                service( Parameters::class ),
                service( Pathfinder::class ),
                service( 'logger' ),
                [
                    param( 'dir.templates' ),
                    param( 'dir.core.templates' ),
                ],
                [service( ViewComponentExtension::class )],
                param( 'kernel.default_locale' ),
                param( 'kernel.debug' ),
            ],
        );
};
