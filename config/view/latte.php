<?php

// -------------------------------------------------------------------
// config\view\latte
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{ComponentFactory, Latte};
use Core\View\Latte\Extension\{CacheExtension, FormatterExtension, OptimizerExtension};
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()->set(
        'settings.latte',
        [
            'autoRefresh' => false,
            'cacheTTL'    => \Cache\AUTO,
        ],
    );

    //
    $container->services()

            // Template cache
        ->set( 'cache.latte', PhpFilesAdapter::class )
        ->args( ['latte', 0, '%dir.cache.latte%'] )
        ->tag( 'cache.pool' )

            //
        ->set( Latte\FrameworkExtension::class )
        ->args( [service( ComponentFactory::class )] )

            //
        ->set( FormatterExtension::class )
            //
            // ->set( OptimizerExtension::class )
            // Cache integration
        ->set( CacheExtension::class )
        ->args(
            [
                service( 'cache.latte' )->nullOnInvalid(),
                service( 'logger' )->nullOnInvalid(),
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'runtime'] )

            // Global Parameters
        ->set( Latte\GlobalVariables::class )
        ->tag( 'core.service_locator' )
        ->args(
            [
                param( 'kernel.environment' ),
                param( 'kernel.debug' ),
                service( 'request_stack' ),
                service( 'security.token_storage' )->ignoreOnInvalid(),
                service( 'security.csrf.token_manager' ),
            ],
        );
};
