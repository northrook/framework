<?php

// -------------------------------------------------------------------
// config\framework\memoization
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Cache\MemoizationCache;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function( ContainerConfigurator $container ) : void {
    //
    $httpCache = [
        'http_event',  // $namespace
        0,             // $defaultLifetime
        '%dir.cache%', // $directory
        true,          // $appendOnly
    ];

    $memoization = [
        'memoization', // $namespace
        0,             // $defaultLifetime
        '%dir.cache%', // $directory
    ];

    $container->services()
            // httpCache
        ->set( 'cache.core.http_event', PhpFilesAdapter::class )
        ->args( $httpCache )
        ->tag( 'cache.pool' )
            // memoization
        ->set( 'cache.memoize', PhpFilesAdapter::class )
        ->args( $memoization )
        ->tag( 'cache.pool' )

            // facade
        ->set( MemoizationCache::class )
        ->args(
            [
                service( 'cache.memoize' ),
                service( 'logger' ),
                param( 'kernel.debug' ),
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'cache'] );
};
