<?php

// -------------------------------------------------------------------
// config\framework\pathfinder
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\{Pathfinder, PathfinderCache, PathfinderInterface};
use Core\Symfony\DependencyInjection\CompilerPass;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( PathfinderCache::class )
        ->args(
            [
                '%kernel.cache_dir%/pathfinder.cache', // $storagePath,
                PathfinderCache::class,                // $name = null,
                false,                // $readonly = false,
                true,                // $autosave = true,
                service( 'logger' ), // $logger = null,
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'pathfinder'] )
            //
            // Find and return registered paths
        ->set( Pathfinder::class )
        ->args(
            [
                CompilerPass::PLACEHOLDER_ARRAY, // $parameters
                service( 'parameter_bag' ),  // $parameterBag
                service( PathfinderCache::class ),                    // $cache
                service( 'logger' ),
                // $logger
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'pathfinder'] )
        ->alias( PathfinderInterface::class, Pathfinder::class );
};
