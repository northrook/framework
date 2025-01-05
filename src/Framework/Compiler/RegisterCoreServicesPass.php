<?php

declare(strict_types=1);

namespace Core\Framework\Compiler;

use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};
use Core\Symfony\Console\{ListReport};
use Core\Symfony\DependencyInjection\CompilerPass;
use Core\Symfony\Interface\ServiceContainerInterface;
use function Support\implements_interface;

final class RegisterCoreServicesPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        if ( ! $container->hasDefinition( 'core.service_locator' ) ) {
            $this->console->error( $this::class." cannot find required 'core.service_locator' definition." );
            return;
        }

        $this->registerTaggedServices( $container );
        $this->injectServiceLocator( $container );
    }

    private function registerTaggedServices( ContainerBuilder $container ) : void
    {
        $serviceLocatorArguments = $container->getDefinition( 'core.service_locator' )->getArguments()[0] ?? [];

        foreach ( $container->findTaggedServiceIds( 'core.service_locator' ) as $id => $unused ) {
            $taggedService = $container->getDefinition( $id );
            $serviceId     = $taggedService->innerServiceId ?? $taggedService->getClass();
            if ( $serviceId ) {
                $serviceLocatorArguments[$id] = new Reference( $serviceId );
            }
            else {
                $this->console->error(
                    $this::class." could not find a serviceId for '{$id}' when parsing services tagged with 'core.service_locator'.",
                );
            }
        }

        $container->getDefinition( 'core.service_locator' )->setArguments( [$serviceLocatorArguments] );
    }

    private function injectServiceLocator( ContainerBuilder $container ) : void
    {
        $coreServiceLocator = $container->getDefinition( 'core.service_locator' );
        $registeredServices = new ListReport( __METHOD__ );

        foreach ( $this->getDeclaredClasses() as $class ) {
            if (
                implements_interface( $class, ServiceContainerInterface::class )
                && $container->hasDefinition( $class )
            ) {
                $registeredServices->item( $class );
                $container->getDefinition( $class )
                    ->addMethodCall(
                        'setServiceLocator',
                        [$coreServiceLocator],
                    );
            }
        }

        $registeredServices->output();
    }
}
