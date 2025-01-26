<?php

declare(strict_types=1);

namespace Symfony\Config;

use Core\Security\{AuthorizationCheckpoint, Entity\User, LoginAuthenticator};
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;

/**
 * @param SecurityConfig $security
 *
 * @return void
 */
return static function( SecurityConfig $security ) : void {
    /**
     * https://symfony.com/doc/current/security/access_control.html
     */
    $security->accessControl();

    $security->passwordHasher(
        PasswordHasherAwareInterface::class,
        'auto',
    );

    $security->firewall( 'main' )
        ->lazy( true )
        ->provider( 'user_provider' )
        ->customAuthenticators( [LoginAuthenticator::class] )
        ->entryPoint( AuthorizationCheckpoint::class )
        ->logout( ['path' => 'auth:logout'] )
        ->rememberMe(
            [
                'secret'   => '%kernel.secret%',
                'lifetime' => 86_400 * 30,
                'path'     => '/',
            ],
        );

    $security->provider(
        'user_provider',
        [
            'entity' => [
                'class'    => User::class,
                'property' => User::ID,
            ],
        ],
    );
};
