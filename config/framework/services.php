<?php

// -------------------------------------------------------------------
// config\framework\services
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Action\Headers;
use Core\View\{Document, Parameters};
use Core\Pathfinder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

return static function( ContainerConfigurator $container ) : void {
    $services = $container->services();

    /** @used-by \Core\Symfony\DependencyInjection\ServiceContainer */
    $container->services()
        ->set( 'core.service_locator' )
        ->tag( 'container.service_locator' )
        ->args(
            [
                [
                    Pathfinder::class => service( Pathfinder::class ),
                    // Document::class   => service( Document::class ),
                    Parameters::class => service( Parameters::class ),
                    // Headers::class    => service( Headers::class ),
                    // Settings::class => service( Settings::class ),

                    // Symfony
                    RequestStack::class          => service( 'request_stack' ),
                    ParameterBagInterface::class => service( 'parameter_bag' ),
                    RouterInterface::class       => service( 'router' ),
                    HttpKernelInterface::class   => service( 'http_kernel' ),
                    SerializerInterface::class   => service( 'serializer' ),

                    // Security
                    TokenStorageInterface::class => service(
                        'security.token_storage',
                    )->nullOnInvalid(),
                    CsrfTokenManagerInterface::class => service(
                        'security.csrf.token_manager',
                    )->nullOnInvalid(),
                    AuthorizationCheckerInterface::class => service(
                        'security.authorization_checker',
                    ),
                ],
            ],
        );
};
