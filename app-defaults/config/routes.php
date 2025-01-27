<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

return static function( RoutingConfigurator $routes ) : void {
    $appControllers = [
        'path'      => '../src/Controller/',
        'namespace' => 'App\Controller',
    ];

    $coreControllers = [
        'path'      => '@CoreBundle/src/Controller',
        'namespace' => 'Core\Controller',
    ];

    $routes->import( $appControllers, 'attribute' );
    $routes->import( $coreControllers, 'attribute' );
};
