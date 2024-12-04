<?php

// -------------------------------------------------------------------
// config\framework\http
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
            //
        ->set( \Core\HTTP\RequestListener::class )
        ->tag( 'kernel.event_subscriber' )
        ->tag( 'monolog.logger', ['channel' => 'http'] )

            //
        ->set( \Core\HTTP\ResponseListener::class )
        ->tag( 'kernel.event_subscriber' )
        ->tag( 'monolog.logger', ['channel' => 'http'] );
};
