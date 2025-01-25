<?php

declare(strict_types=1);

namespace Symfony\Config;

return static function( FrameworkConfig $framework ) : void {
    $framework->cache()
        ->app( 'cache.adapter.filesystem' )
        ->system( 'cache.adapter.system' );
};
