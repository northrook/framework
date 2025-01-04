<?php

// -------------------------------------------------------------------
// config\framework\http
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Clerk;
use Core\Service\AssetManager;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
            // Cache
        ->set( 'cache.core.http_event', PhpFilesAdapter::class )
        ->args( ['http_event', 0, '%dir.cache%', true] )
        ->arg( '$appendOnly', true )
        ->tag( 'cache.pool' );

    // Cache
    // ->set( 'cache.core.request_response', PhpFilesAdapter::class )
    // ->args( ['memoization', 0, '%dir.cache%'] )
    // ->tag( 'cache.pool' )
    //
    //     //
    // ->set( \Core\Http\RequestListener::class )
    // ->args( [service( Clerk::class ), service( 'cache.core.request_response' ), service( 'logger' )] )
    // ->tag( 'kernel.event_subscriber' )
    // ->tag( 'monolog.logger', ['channel' => 'http'] )
    //     //
    // ->set( \Core\Http\ResponseListener::class )
    // ->args( [service( Clerk::class ), service( 'cache.core.request_response' ), service( 'logger' )] )
    // ->tag( 'kernel.event_subscriber' )
    // ->tag( 'monolog.logger', ['channel' => 'http'] );

    // Sending HTML Response
    // ->set( DocumentView::class )
    // ->args(
    //     [
    //         service( Document::class ),
    //         service( AssetManager::class ),
    //     ],
    // )->tag( 'core.service_locator' );
};
