<?php

// -------------------------------------------------------------------
// config\view\components
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\ComponentFactory;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( ComponentFactory::class )
        ->args( [[]] );
};
