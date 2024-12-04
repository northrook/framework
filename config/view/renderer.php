<?php

// -------------------------------------------------------------------
// config\view\renderer
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Service\IconService;
use Core\View\{
        Latte,
    Template\Extension\IconPackExtension,
    Template\TemplateCompiler
};

return static function( ContainerConfigurator $container ) : void {
    //
    $container->services()
            //
        ->set( IconService::class )
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
                    service( IconPackExtension::class ),
                ], // $extensions
                [
                    'get' => service( Latte\GlobalVariables::class ),
                ], // $variables
            ],
        );
};
