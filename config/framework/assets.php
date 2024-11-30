<?php

// -------------------------------------------------------------------
// config\framework\assets
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Service\AssetBundler;
use Core\Symfony\DependencyInjection\CompilerPass;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( AssetBundler::class )
        ->args( ['%kernel.cache_dir%/asset.manifest.php', CompilerPass::PLACEHOLDER_ARG] )
        ->tag( 'controller.service_arguments' )
        ->autowire();
};