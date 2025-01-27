<?php

declare(strict_types=1);

namespace Symfony\Config;

use Core\Security\{AccessCheckpoint, AccessDenied, Entity\User, LoginAuthenticator};
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use const Time\MONTH;

/**
 * @param SecurityConfig $security
 *
 * @return void
 */
return static function( SecurityConfig $security ) : void {
    /**
     * https://symfony.com/doc/current/security/access_control.html
     */
    // $security->accessControl();

    $security->passwordHasher(
        PasswordHasherAwareInterface::class,
        'auto',
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

    $nullFirewall = $security
        ->firewall( 'null' )
        ->pattern( '^/(_(profiler|wdt)|css|images|js)/' )
        ->security( false );

    $mainFirewall = $security
        ->firewall( 'main' )
        ->lazy( true );

    $mainFirewall
        ->provider( 'user_provider' )
        ->entryPoint( AccessCheckpoint::class )
        ->accessDeniedHandler( AccessDenied::class )
        ->logout( ['path' => 'auth:logout'] );

    $mainFirewall
        ->customAuthenticators( [LoginAuthenticator::class] )
        ->rememberMe()
        ->secret( '%kernel.secret%' )
        ->lifetime( MONTH )
        ->path( '/' );
};
