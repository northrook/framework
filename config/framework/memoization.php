<?php

// -------------------------------------------------------------------
// config\framework\memoization
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Cache\MemoizationCache;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
            // Cache
        ->set( 'cache.memoize', PhpFilesAdapter::class )
        ->args( ['memoization', 0, '%dir.cache%'] )
        ->tag( 'cache.pool' )

            //
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
