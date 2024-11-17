<?php

// -------------------------------------------------------------------
// config\view\renderer
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{Controller\ResponseRenderer, IconRenderer, Latte, TemplateEngine};

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
        ->set( TemplateEngine::class )
        ->tag( 'core.service_locator' )
        ->args(
            [
                '%dir.root%', // $projectDirectory
                [
                    '%dir.templates%',
                    '%dir.core.templates%',
                ], // $templateDirectories
                '%dir.cache.latte%', // $cacheDirectory
                '%kernel.default_locale%', // $locale
                '%kernel.debug%', // $autoRefresh
                [
                    service( Latte\FrameworkExtension::class ),
                    service( Latte\Extension\FormatterExtension::class ),
                    service( Latte\Extension\OptimizerExtension::class ),
                    service( Latte\Extension\CacheExtension::class ),
                ], // $extensions
                [
                    'get' => service( Latte\GlobalVariables::class ),
                ], // $variables
            ],
        );
};
