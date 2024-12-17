<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Pathfinder;
use Core\Service\AssetManager;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
            // Asset Manager Services
        ->defaults()->autoconfigure()
            //
            //
            // Manifest
        ->set( AssetManager\AssetManifest::class )
        ->args(
            [
                param( 'path.asset_manifest' ),
                service( 'logger' ),
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'asset_manager'] )
            //
            //
            // Locator
        ->set( AssetManager\AssetLocator::class )
        ->args(
            [
                service( Pathfinder::class ),
                service( AssetManager\AssetManifest::class ),
                service( 'logger' ),
            ],
        )
        ->tag( 'monolog.logger', ['channel' => 'asset_manager'] )
            //
            //
            // Manager
        ->set( AssetManager::class )
        ->args(
            [
                service( AssetManager\AssetManifest::class ),
                service( AssetManager\AssetLocator::class ),
                service( Pathfinder::class ),
                null, // $settings
                null, // $cache
                service( 'logger' ),
            ],
        )
        ->tag( 'core.service_locator' )
        ->tag( 'monolog.logger', ['channel' => 'asset_manager'] );
};
