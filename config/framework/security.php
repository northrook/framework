<?php

// -------------------------------------------------------------------
// config\framework\security
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Security;

return static function( ContainerConfigurator $container ) : void {
    $container->services()

            // Toast Flashbag Handler
        ->set( Security::class )
        ->args( [service( 'security.authorization_checker' )] )
        ->tag( 'core.service_locator' );
};
