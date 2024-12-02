<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Service\{AssetBundler, AssetLocator};
use Core\Symfony\DependencyInjection\CompilerPass;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()
        ->set( ...AssetBundler\Config::stylesheet( 'core' ) );

    $container->services()
            // AssetManifest
        ->set( AssetLocator::class )
        ->args( [service( AssetBundler\AssetManifest::class )] )
        ->tag( 'core.service_locator' )
            //

            // AssetManifest
        ->set( AssetBundler\AssetManifest::class )
        ->args( ['%path.asset_manifest%', 'AssetManifest'] )
            //
        ->set( AssetBundler::class )
        ->args(
            [
                service( AssetBundler\AssetManifest::class ),
                CompilerPass::PLACEHOLDER_ARG,
                param( 'kernel.build_dir' ),
            ],
        )
        ->tag( 'controller.service_arguments' )
        ->autowire();
};
