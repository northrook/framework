<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Assets\{AssetFactory, AssetManifest, Factory\Asset\Type, Factory\AssetReference};
use Core\Service\AssetManager;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()->set(
        'config.asset.core_styles',
        AssetReference::config(
            'core.styles',
            Type::STYLE,
            'dir.assets/styles/core',
            'dir.core.assets/styles/core',
        ),
    );

    $container->services()
        ->set( AssetManifest::class )
        ->arg( 0, param( 'path.asset_manifest' ) )

            // Asset Manager Services
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
};
