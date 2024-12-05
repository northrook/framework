<?php

// -------------------------------------------------------------------
// config\framework\http
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\View\DocumentView;
use Northrook\Clerk;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
            //
        ->set( \Core\HTTP\RequestListener::class )
        ->args( [service( Clerk::class )] )
        ->tag( 'kernel.event_subscriber' )
        ->tag( 'monolog.logger', ['channel' => 'http'] )

            //

            // Sending HTML Response
        // ->set( DocumentView::class )
        // ->args([])

            //
        ->set( \Core\HTTP\ResponseListener::class )
        ->args( [service( Clerk::class )] )
        ->tag( 'kernel.event_subscriber' )
        ->tag( 'monolog.logger', ['channel' => 'http'] );
};
