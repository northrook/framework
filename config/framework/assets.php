<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Assets\{AssetFactory,
    AssetManager,
    AssetManifest,
    Interface\AssetManifestInterface
};

return static function( ContainerConfigurator $container ) : void {
    /**
     * Register AssetManifest as a service
     */
    $container->services()
        ->set( AssetManifest::class )
        ->args( [param( 'path.asset_manifest' )] )
        ->tag( 'monolog.logger', ['channel' => 'asset_manager'] )
        ->alias( AssetManifestInterface::class, AssetManifest::class );

    $container->services()
        ->set( AssetFactory::class );

    $container->services()
            //
            // Framework Asset Manager
        ->set( AssetManager::class )
        ->args(
            [
                service( AssetFactory::class ),
                null, // cache
                service( 'logger' ),
            ],
        )
        ->tag( 'core.service_locator' );
};
