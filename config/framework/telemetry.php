<?php

// -------------------------------------------------------------------
// config\framework\telemetry
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Symfony\Profiler\ParameterBagCollector;
use Northrook\Clerk;
use Core\Framework\Telemetry\{ClerkProfiler, PipelineCollector};
use Symfony\Component\Stopwatch\Stopwatch;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
            // Stopwatch
        ->set( Clerk::class )
        ->args(
            [
                service( Stopwatch::class ),
                true, // single instance
                true, // throw on reinstantiation attempt
                param( 'kernel.debug' ), // only enable when debugging
            ],
        )

            // TelemetryEventSubscriber
        ->set( ClerkProfiler::class )
        ->tag( 'kernel.event_subscriber' )
        ->args( [service( Clerk::class )] )

            // Profiler
        ->set( PipelineCollector::class )
        ->tag( 'data_collector' )
            //
        ->set( ParameterBagCollector::class )
        ->args( [service( 'parameter_bag' )] )
        ->tag( 'data_collector' );
};
