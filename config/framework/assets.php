<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Pathfinder;
use Core\Service\AssetManager;
use Core\Service\AssetManager\Compiler\Register;
use Core\Symfony\DependencyInjection\CompilerPass;

return static function( ContainerConfigurator $container ) : void {
    // ??  Create set of predefined built-in assets
    //     for admin and core
    //     public is handled by ./app/assets/styles|scripts/*

    // ::  ad-hoc assets like images, cdn, etc, needs to auto-generate

    $container->parameters()
        ->set(
            'asset.core.style',
            Register::stylesheet(
                'core.style',
                'dir.core.assets/core/*.css',
                AssetManager\Asset\Source::LOCAL,
            ),
        )
        ->set( 'dir.asset_source.app', '%kernel.project_dir%/assets/' )
        ->set( 'dir.asset_source.core', \dirname( __DIR__, 2 ).'/assets/' )
        ->set( 'dir.asset_source.components', \dirname( __DIR__, 2 ).'/components/' );

    $container->services()
        ->set( AssetManager\AssetCompiler::class )
        ->args( [service( Pathfinder::class )] )
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
