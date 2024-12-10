<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Service\AssetManager;
use Core\Service\AssetManager\Model\StyleAsset;
use Core\Symfony\DependencyInjection\CompilerPass;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()
        ->set(
            'register_asset.public',
            StyleAsset::register(
                'public',
                \Support\FileScanner::get(
                    \dirname( __DIR__, 3 ).'/assets/styles',
                    'css',
                    recursion : true,
                ),
            ),
        );

    $container->services()

            //
        ->set( AssetManager\AssetFactory::class )
        ->args(
            [
                CompilerPass::PLACEHOLDER_ARG,
                // '%path.asset_manifest%',
            ],
        )
            //
        ->set( AssetManager::class )
        ->args(
            [
                service( AssetManager\AssetFactory::class ),
                service( 'logger' ),
            ],
        )
        ->tag( 'core.service_locator' )
        ->tag( 'monolog.logger', ['channel' => 'assets'] );
    //
    //
    // ->set( AssetBundler::class )
    // ->args(
    //     [
    //         service( AssetBundler\AssetManifest::class ),
    //         CompilerPass::PLACEHOLDER_ARG,
    //         param( 'kernel.build_dir' ),
    //     ],
    // )
    // ->tag( 'controller.service_arguments' )
    // ->autowire();
};
