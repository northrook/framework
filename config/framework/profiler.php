<?php

// -------------------------------------------------------------------
// config\framework\profiler
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// use Core\Framework\Settings;
use Northrook\Clerk;
use Core\Framework\Profiler\{ClerkProfiler, ParameterSettingsCollector, PipelineCollector};
use Symfony\Component\Stopwatch\Stopwatch;

return static function( ContainerConfigurator $container ) : void {
    $container->services()
        ->set( Clerk::class )
        ->args(
            [
                service( Stopwatch::class ),
                service( 'logger' ),
                param( 'kernel.debug' ), // enabled when debugging, regardless of env
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
        ->set( ParameterSettingsCollector::class )
        ->args(
            [
                service( 'parameter_bag' ),
                // service( Settings::class )
            ],
        )
        ->tag(
            'data_collector',
            [
                'template' => '@Core/profiler/parameter_settings.html.twig',
                'priority' => 240,
            ],
        );
};
