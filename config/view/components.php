<?php

// -------------------------------------------------------------------
// config\view\components
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\ComponentFactory;
use Support\ClassInfo;

return static function( ContainerConfigurator $container ) : void {
    // $components = Configure::registerComponents( \dirname( __DIR__, 2 ).'/src/Component/*.php' );

    $components = [];

    // foreach ( \glob( \dirname( __DIR__, 2 ).'/src/Component/*.php' ) ?: [] as $filePath ) {
    //     $component                     = new ClassInfo( $filePath );
    //     $components[$component->class] = $component;
    // }

    $container->services()
        ->set( ComponentFactory::class )
        ->args( [$components] );
};å
