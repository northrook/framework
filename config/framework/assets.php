<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Pathfinder;
use Core\Service\AssetManager;
use Core\Symfony\DependencyInjection\CompilerPass;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
            // Manifest
        ->set( AssetManager\AssetManifest::class )
        ->args( [param( 'path.asset_manifest' ), service( 'logger' )] )
        ->tag( 'monolog.logger', ['channel' => 'asset_manifest'] )
        ->autoconfigure()
            //
        ->set( AssetManager\AssetCompiler::class )
        ->args(
            [
                service( Pathfinder::class ),
                service( AssetManager\AssetManifest::class ),
                service( 'logger' ),
            ],
        )
            //
        ->set( AssetManager\AssetFactory::class )
        ->args(
            [
                '%kernel.project_dir%/public/assets/',
                '%kernel.project_dir%/var/assets/',
                CompilerPass::PLACEHOLDER_ARG,
                // '%path.asset_manifest%',
            ],
        )
            //
        ->set( AssetManager::class )
        ->args(
            [
                service( AssetManager\AssetCompiler::class ),
                service( 'logger' ),
            ],
        )
        ->tag( 'core.service_locator' )
        ->tag( 'monolog.logger', ['channel' => 'assets'] );
};
