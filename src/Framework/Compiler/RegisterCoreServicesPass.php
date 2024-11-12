<?php

declare(strict_types=1);

namespace Core\Framework\Compiler;

use Core\Symfony\DependencyInjection\CompilerPass;
use Core\Framework\DependencyInjection\ServiceContainer;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};
use function Support\uses_trait;

final class RegisterCoreServicesPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        if ( ! $container->hasDefinition( 'core.service_locator' ) ) {
            // TODO : Console message
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

        foreach ( $this->getDeclaredClasses( $container->getServiceIds() ) as $class ) {
            if (
                uses_trait( $class, ServiceContainer::class, true )
                && $container->hasDefinition( $class )
            ) {
                $this->console->success( "{$class}::setServiceLocator.\n" );
                $container->getDefinition( $class )
                    ->addMethodCall(
                        'setServiceLocator',
                        [$coreServiceLocator],
                    );
            }
        }
    }

    /**
     * @param string[] $services
     *
     * @return array<int, class-string>
     */
    private function getDeclaredClasses( array $services ) : array
    {
        return \array_values(
            \array_unique(
                [
                    ...\get_declared_classes(),
                    ...\array_filter( $services, 'class_exists' ),
                ],
            ),
        );
    }
}
