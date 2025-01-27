<?php

declare(strict_types=1);

namespace Symfony\Config;

return static function( FrameworkConfig $framework ) : void {
    $cache = $framework->cache();

    $cache
        ->app( 'cache.adapter.filesystem' )
        ->system( 'cache.adapter.system' );

    $cache
        ->pool( 'doctrine.result_cache_pool' )
        ->adapters( 'cache.app' );
    $cache
        ->pool( 'doctrine.system_cache_pool' )
        ->adapters( 'cache.system' );
};
