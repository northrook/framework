<?php

// -------------------------------------------------------------------
// config\framework\http
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Response\Document;
use Core\View\{ComponentFactory, DocumentView};
use Core\Service\{AssetLocator};
use Northrook\Clerk;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
            // Cache
        ->set( 'cache.core.request_response', PhpFilesAdapter::class )
        ->args( ['memoization', 0, '%dir.cache%'] )
        ->tag( 'cache.pool' )

            //
        ->set( \Core\HTTP\RequestListener::class )
        ->args( [service( Clerk::class ), service( 'cache.core.request_response' )] )
        ->tag( 'kernel.event_subscriber' )
        ->tag( 'monolog.logger', ['channel' => 'http'] )

            //

            // Sending HTML Response
        ->set( DocumentView::class )
        ->args(
            [
                service( Document::class ),
                service( ComponentFactory::class ),
                service( AssetLocator::class ),
            ],
        )

            //
        ->set( \Core\HTTP\ResponseListener::class )
        ->args( [service( Clerk::class ), service( 'cache.core.request_response' )] )
        ->tag( 'kernel.event_subscriber' )
        ->tag( 'monolog.logger', ['channel' => 'http'] );
};
