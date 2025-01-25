<?php

// -------------------------------------------------------------------
// config\framework\security
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Framework\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

return static function( ContainerConfigurator $container ) : void {
    $container->extension(
        'security',
        [
            'password_hashers' => [PasswordAuthenticatedUserInterface::class => 'auto'],
            'firewalls'        => [
                'main' => [
                    'lazy'     => true,
                    'provider' => 'app_user_provider',
                    // 'custom_authenticator' => LoginAuthenticator::class,
                    // 'entry_point'          => AuthorizationCheckpoint::class,
                    'logout' => [
                        'path' => 'auth:logout',
                    ],
                ],
            ],
            'providers' => [
                'app_user_provider' => [
                    'entity' => [
                        // 'class'    => User::class,
                        // 'property' => 'email',
                    ],
                ],
            ],
        ],
    );

    $container->services()

            // Toast Flashbag Handler
        ->set( Security::class )
        ->args( [service( 'security.authorization_checker' )] )
        ->tag( 'core.service_locator' );
};
