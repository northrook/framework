<?php

// -------------------------------------------------------------------
// config\view\renderer
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{Controller\ResponseRenderer, IconRenderer, Latte, Template\TemplateCompiler};

return static function( ContainerConfigurator $container ) : void {
    //
    $container->services()
            //
        ->set( ResponseRenderer::class )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.response'] )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.exception'] )

            //
        ->set( IconRenderer::class )
        ->tag( 'core.service_locator' )
        ->autowire()

            //
        ->set( TemplateCompiler::class )
        ->tag( 'core.service_locator' )
        ->args(
            [
                ['%dir.templates%', '%dir.core.templates%'], // $viewDirectories
                '%dir.cache.latte%', // $cacheDirectory
                '%kernel.default_locale%', // $locale
                '%kernel.debug%', // $autoRefresh
                [
                    service( Latte\FrameworkExtension::class ),
                    service( Latte\Extension\CacheExtension::class ),
                ], // $extensions
                [
                    'get' => service( Latte\GlobalVariables::class ),
                ], // $variables
            ],
        );
};
