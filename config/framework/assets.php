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
        ->arg( 0, service( AssetFactory::class ) )
            // ->arg( 1, service( [Cache] ) )
        ->arg( 2, service( 'logger' ) )
        ->tag( 'core.service_locator' )
            //
            // Factory & Locator
        ->set( AssetFactory::class )
        ->arg( 0, service( AssetManifest::class ) )
        ->arg( 1, service( Pathfinder::class ) )
            // ->arg( 2, service( [Settings] ) )
        ->arg( 3, service( 'logger' ) )
            //
            // Manifest
        ->set( AssetManifest::class )
        ->arg( 0, param( 'path.asset_manifest' ) )
        ->arg( 1, service( 'logger' ) );
};
