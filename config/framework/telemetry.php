<?php

// -------------------------------------------------------------------
// config\framework\telemetry
// -------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Telemetry\ClerkProfiler;
use Core\Framework\Telemetry\PipelineCollector;
use Northrook\Clerk;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Stopwatch\Stopwatch;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function( ContainerConfigurator $container ) : void
{
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
              ->tag( 'data_collector' );

};
