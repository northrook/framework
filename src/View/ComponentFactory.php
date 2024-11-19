<?php

declare(strict_types=1);

namespace Core\View;

use Core\Framework\DependencyInjection\ServiceContainer;
use Core\View\ComponentFactory\ComponentProperties;
use Core\View\Exception\ComponentNotFoundException;
use Core\View\Component\ComponentInterface;
use Northrook\Logger\{Level, Log};
use Support\Arr;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Polyfill\Intl\Icu\Exception\NotImplementedException;
use function Support\{classBasename};
use const Cache\AUTO;

final class ComponentFactory
{
    use ServiceContainer;

    /** @var array<string, ComponentProperties> */
    private array $propertiesCache = [];

    /**
     * `[ className => uniqueId ]`.
     *
     * @var array<class-string|string, array<int, string>>
     */
    private array $instantiated = [];

    /**
     * Provide a [class-string, args[]] array.
     *
     * @param array<class-string, array{name: string, class: class-string, render: 'live'|'runtime'|'static', tags: string[], tagged: array<string, ?string[]>} > $components
     * @param array                                                                                                                                               $tags
     * @param ServiceLocator                                                                                                                                      $componentLocator
     */
    public function __construct(
        private readonly array          $components,
        private readonly array          $tags,
        private readonly ServiceLocator $componentLocator,
    ) {
    }

    /**
     * Check if the provided string matches any {@see ComponentFactory::$tags}.
     *
     * @param string $tag
     *
     * @return bool
     */
    public function hasTag( string $tag ) : bool
    {
        return \array_key_exists( $tag, $this->tags );
    }

    /**
     * Retrieve {@see ComponentProperties} by `name`, `className`, or `tag`.
     *
     * Returns `null` if the resulting component does not exist.
     *
     * @param string $get
     *
     * @return ?ComponentProperties
     */
    public function getComponentProperties( string $get ) : ?ComponentProperties
    {
        $component = $this->getComponentName( $get );

        if ( ! $component ) {
            return null;
        }

        return $this->propertiesCache[$component] ??= new ComponentProperties( ...$this->components[$component] );
    }

    /**
     * Begin the Build proccess of a component.
     *
     * @param ComponentProperties|string $component
     *
     * @return ComponentInterface
     */
    public function build( string|ComponentProperties $component ) : mixed
    {
        if ( ! $this->hasComponent( $component ) ) {
            throw new ComponentNotFoundException( $component );
        }

        if ( $this->componentLocator->has( $component ) ) {
            $component = $this->componentLocator->get( $component );

            \assert( $component instanceof ComponentInterface );

            return $component;
        }

        throw new ComponentNotFoundException( $component, 'Not found in the Component Container.' );
    }

    /**
     * Renders a component at runtime.
     *
     * @param class-string|string  $component
     * @param array<string, mixed> $arguments
     * @param ?int                 $cache
     *
     * @return string
     */
    public function render( string $component, array $arguments = [], ?int $cache = AUTO ) : string
    {
        $properties = $this->getComponentProperties( $component );

        if ( ! $properties ) {
            Log::exception( new ComponentNotFoundException( $component ), Level::CRITICAL );
            return '';
        }

        $tag = $arguments['tag'] ?? null;

        if ( isset( $arguments['tag'] ) ) {
            $this->parseTaggedAttributes( $arguments, $properties->tagged );
        }

        $component = $this->build( $component );
        $component->build( $arguments );

        dump( $component );

        $this->instantiated[$component][] = $component->componentUniqueId();
        return $component->render();
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

    private function parseTaggedAttributes( array &$arguments, array $promote = [] ) : void
    {
        $exploded         = \explode( ':', $arguments['tag'] );
        $arguments['tag'] = $exploded[0];

        $promote = $promote[$arguments['tag']] ?? null;

        foreach ( $exploded as $position => $tag ) {
            if ( $promote && $promote[$position] ?? false ) {
                $arguments[$promote[$position]] = $tag;
                unset( $arguments[$position] );

                continue;
            }
            if ( $position ) {
                $arguments[$position] = $tag;
            }
        }
    }

    /**
     * @return array<class-string, array<int, string>>
     */
    public function getInstantiated() : array
    {
        return $this->instantiated;
    }

    /**
     * @return array<int, string>
     */
    public function getInstantiatedComponents() : array
    {
        return \array_keys( $this->instantiated );
    }

    /**
     */
    public function getRegisteredComponents() : array
    {
        return $this->components;
    }

    /**
     * @param string $value
     *
     * @return null|string
     */
    public function getComponentName( string $value ) : ?string
    {
        // If the provided $value matches an array name, return it
        if ( \array_key_exists( $value, $this->components ) ) {
            return $value;
        }

        // If the $value is a class-string, the class exists, and is a component, return the name
        if ( \str_contains( $value, '\\' ) && \class_exists( $value ) ) {
            return Arr::search( $this->components, $value );
        }

        // Parsed namespaced tag $value
        if ( \str_contains( $value, ':' ) ) {
            if ( \str_starts_with( $value, 'ui:' ) ) {
                $value = \substr( $value, 3 );
            }

            $value = \strstr( $value, ':', true ) ?: $value;
        }

        return $this->tags[$value] ?? null;
    }

    public function hasComponent( string $component ) : bool
    {
        return \array_key_exists( $component, $this->components );
    }

    // /**
    //  * @param class-string $class
    //  * @param mixed        ...$args
    //  *
    //  * @return ComponentInterface
    //  */
    // public function create( string $class, mixed ...$args ) : ComponentInterface
    // {
    //     $component = $this->intantiate( $class, $args );
    //
    //     dump( $component );
    //     // if ( $component instanceof AutowireServicesInterface ) {
    //     //     // TODO : Look into using Reflect instead
    //     //     foreach ( $component->getAutowireServices() as $property => $autowireService ) {
    //     //         $component->setAutowireService( $property, $this->serviceLocator( $autowireService ) );
    //     //     }
    //     // }
    //
    //     return $component;
    // }

}
