<?php

// -------------------------------------------------------------------
// config\framework\settings
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\{Pathfinder, Settings};

return static function( ContainerConfigurator $container ) : void {
    foreach ( [
        'dir.root'   => '%kernel.project_dir%',
        'dir.var'    => '%dir.root%/var',
        'dir.public' => '%dir.root%/public',
        'dir.core'   => \dirname( __DIR__, 2 ),

        // Assets
        'dir.assets'         => '%dir.root%/assets',
        'dir.public.assets'  => '%dir.root%/public/assets',
        'dir.assets.storage' => '%dir.root%/var/assets',
        'dir.core.assets'    => '%dir.core%/assets',
        'dir.assets.themes'  => '%dir.core%/assets',
        //
        'path.asset_manifest' => '%dir.root%/var/assets/manifest.array.php',

        // Templates
        'dir.templates'      => '%dir.root%/templates',
        'dir.core.templates' => '%dir.core%/templates',

        // Cache
        'dir.cache'       => '%kernel.cache_dir%',
        'dir.cache.latte' => '%kernel.cache_dir%/latte',

        // Themes
        'path.theme.core' => '%dir.core%/config/themes/core.php',

        // Settings DataStore
        'path.settings_store' => '%dir.var%/settings.array.php',

    ] as $name => $value ) {
        $container->parameters()->set( $name, Pathfinder::normalize( $value ) );
    }

    $container->services()
            // Settings handler
        ->set( Settings::class )
        ->args( [param( 'path.settings_store' )] )
        ->tag( 'controller.service_arguments' )
        ->autowire();
};
