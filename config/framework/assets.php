<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Assets\{AssetFactory, AssetManifest};
use Core\Pathfinder;
use Core\Service\AssetManager;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
            // Asset Manager Services
        ->defaults()
        ->autoconfigure()
        ->tag( 'monolog.logger', ['channel' => 'asset_manager'] )
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
    //
    // Factory & Locator
    // ->set( AssetFactory::class )
    // ->args(
    //     [
    //         service( AssetManifest::class ),
    //         service( Pathfinder::class ),
    //         service( 'logger' ),
    //     ],
    // );
    //
    // Manifest
    // ->set( AssetManifest::class )
    // ->arg( 0, param( 'path.asset_manifest' ) )
    // ->arg( 1, service( 'logger' ) );
};
