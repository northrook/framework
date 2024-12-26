<?php

// -------------------------------------------------------------------
// config\view\renderer
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\{Pathfinder, View\Config};
use Core\Service\IconService;
use Core\TemplateEngine;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()->set(
        'view.template_engine',
        Config::templateEngine(
            cacheDirectory      : '%dir.cache.view%',
            templateDirectories : ['%dir.templates%', '%dir.core.templates%'],
        ),
    );

    //
    $container->services()
            // ->defaults()
            // ->autoconfigure()
            // ->load( 'Core\\View\\Component\\', dirname( __DIR__, 2 ) . '/components/' )
        ->set( IconService::class )
        ->tag( 'core.service_locator' )
        ->autowire()

            //
        ->set( TemplateEngine::class )
        ->tag( 'core.service_locator' )
        ->arg( '$pathfinder', service( Pathfinder::class ) )
        ->arg( '$configuration', param( 'view.template_engine' ) )
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
