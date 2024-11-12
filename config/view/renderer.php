<?php

// -------------------------------------------------------------------
// config\view\renderer
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\{ComponentFactory, Latte\FrameworkExtension, Latte\GlobalVariables, ResponseRenderer, TemplateEngine};

return static function( ContainerConfigurator $container ) : void {
    //
    $container->services()
            //
        ->set( ResponseRenderer::class )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.response'] )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.exception'] )

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
                    service( FrameworkExtension::class ),
                ], // $extensions
                [
                    'get' => service( GlobalVariables::class ),
                ], // $variables
            ],
        )

            //
        ->set( FrameworkExtension::class )
        ->args( [service( ComponentFactory::class )] )

            // Global Parameters
        ->set( GlobalVariables::class )
        ->tag( 'core.service_locator' )
        ->args(
            [
                param( 'kernel.environment' ),
                param( 'kernel.debug' ),
                service( 'request_stack' ),
                service( 'security.token_storage' )->ignoreOnInvalid(),
                service( 'security.csrf.token_manager' ),
            ],
        );
};
