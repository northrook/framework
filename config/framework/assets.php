<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Service\AssetBundler;
use Core\Symfony\DependencyInjection\CompilerPass;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()
        ->set( ...AssetBundler\Config::stylesheet( 'core' ) );

    $container->services()

            // AssetManifest
        ->set( AssetBundler\AssetManifest::class )
        ->args( ['%kernel.cache_dir%/asset.manifest.php', 'AssetManifest'] )
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
