<?php

// -------------------------------------------------------------------
// config\framework\controllers\public
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Controller\{AdminController, FaviconController, PublicController, SecurityController};

return static function( ContainerConfigurator $controller ) : void {
    $framework = $controller->services()
        ->defaults()
        ->tag( 'controller.service_arguments' )
        ->tag( 'monolog.logger', ['channel' => 'request'] );

    $framework->set( SecurityController::class );

    $framework->set( FaviconController::class );

    $framework
        ->set( PublicController::class )
        ->autowire();

    $framework
        ->set( AdminController::class )
        ->autowire();
};
