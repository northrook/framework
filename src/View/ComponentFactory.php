<?php

declare(strict_types=1);

namespace Core\View;

use Core\Framework\DependencyInjection\ServiceContainer;
use Symfony\Polyfill\Intl\Icu\Exception\NotImplementedException;
use function Support\classBasename;

final class ComponentFactory
{
    use ServiceContainer;

    /**
     * `[ className => componentName ]`.
     *
     * @var array<class-string, string>
     */
    private array $instantiated = [];

    /**
     * Provide a [class-string, args[]] array.
     *
     * @param array<class-string, array{name: string, class:class-string, tags: string[]}> $components
     * @param array                                                                        $tags
     */
    public function __construct(
        private readonly array $components = [],
        private readonly array $tags = [],
    ) {
    }

    /**
     * @param class-string $class
     * @param mixed        ...$args
     *
     * @return ComponentInterface
     */
    public function create( string $class, mixed ...$args ) : ComponentInterface
    {
        $component = $this->intantiate( $class, $args );

        dump( $component );
        // if ( $component instanceof AutowireServicesInterface ) {
        //     // TODO : Look into using Reflect instead
        //     foreach ( $component->getAutowireServices() as $property => $autowireService ) {
        //         $component->setAutowireService( $property, $this->serviceLocator( $autowireService ) );
        //     }
        // }

        return $component;
    }

    /**
     * @param class-string $component
     * @param mixed        ...$args
     *
     * @return ComponentInterface
     */
    private function intantiate( string $component, mixed ...$args ) : ComponentInterface
    {
        if ( \class_exists( $component ) && \is_subclass_of( $component, ComponentInterface::class ) ) {
            if ( ! isset( $this->instantiated[$component] ) ) {
                $this->instantiated[$component] = \strtolower( classBasename( $component ) );
            }
            return new $component( ...$args );
        }
        throw new NotImplementedException( ComponentInterface::class );
    }

    /**
     * @return array<class-string, string>
     */
    public function getInstantiatedComponents() : array
    {
        return $this->instantiated;
    }

    /**
     */
    public function getRegisteredComponents() : array
    {
        return $this->components;
    }
}
