<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Assets\{AssetFactory,
    AssetManager,
    AssetManifest,
    CoreStyleFilter,
    Interface\AssetManifestInterface
};
use Core\Pathfinder;

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
        ->set( AssetFactory::class )
            // ->lazy( true )
        ->args(
            [
                service( AssetManifest::class ),
                service( Pathfinder::class ),
                param( 'dir.assets' ),
                [
                    param( 'dir.assets' ),
                    param( 'dir.core.assets' ),
                ],
                service( 'logger' ),
            ],
        )
        ->call(
            'addAssetModelCallback',
            CoreStyleFilter::callback( 'style.core' ),
        );

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
