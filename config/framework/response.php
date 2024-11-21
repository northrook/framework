<?php

// -------------------------------------------------------------------
// config\framework\response
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Controller\{ExceptionListener, ResponseListener};
use Core\Framework\Response\{Document, Headers, Parameters};

return static function( ContainerConfigurator $container ) : void {
    $container->services()

            // ErrorListener
        ->set( ExceptionListener::class )
        ->tag( 'kernel.event_listener' )

            // Response EventSubscriber;
        ->set( ResponseListener::class )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.controller'] )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.view'] )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.response'] )
        ->tag( 'kernel.event_listener', ['event' => 'kernel.terminate'] );

    $container->services()->defaults()
        ->tag( 'controller.service_arguments' )
        ->autowire()

            // ResponseHeaderBag Service
        ->set( Headers::class )

            // Document Properties
        ->set( Document::class )

            // Template Parameters
        ->set( Parameters::class );
};
