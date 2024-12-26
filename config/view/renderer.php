<?php

// -------------------------------------------------------------------
// config\view\renderer
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\{Pathfinder};
use Core\Service\IconService;
use Core\View\{Latte, TemplateEngine};
use function Core\View\config;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()->set(
        'view.template_engine',
        [
            '%kernel.cache_dir%/view',
            ['dir.templates', 'dir.core.templates'],
        ],
    );

    //
    $container->services()
            //
        // ->set( IconService::class )
            // ->tag( 'core.service_locator' )
            // ->autowire()

            //
        ->set( TemplateEngine::class )
        ->tag( 'core.service_locator' )
        ->arg( '$pathfinder', service( Pathfinder::class ) )
        ->arg(
            '$configuration',
            config( '%kernel.cache_dir%/view', ['dir.templates', 'dir.core.templates'] ),
        )
        ->arg( '$logger', service( 'logger' ) );
    // ->args(
    //     [
    //
    //         ['%dir.templates%', '%dir.core.templates%'], // $viewDirectories
    //         '%dir.cache.latte%', // $cacheDirectory
    //         '%kernel.default_locale%', // $locale
    //         '%kernel.debug%', // $autoRefresh
    //         [
    //             service( Latte\FrameworkExtension::class ),
    //             service( Latte\Extension\CacheExtension::class ),
    //             service( IconPackExtension::class ),
    //         ], // $extensions
    //         [
    //             'get' => service( Latte\GlobalVariables::class ),
    //         ], // $variables
    //     ],
    // )
};
