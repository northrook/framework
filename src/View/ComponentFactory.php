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
     * @param array<class-string, array{render: 'live'|'runtime'|'static', taggedProperties: array<array-key,array<int, mixed>>} > $components
     * @param array                                                                                                                $tags
     * @param ServiceLocator                                                                                                       $componentLocator
     */
    public function __construct(
        private readonly array          $components,
        private readonly array          $tags,
        private readonly ServiceLocator $componentLocator,
    ) {
    }

    public function getComponentProperties( string $get ) : ?ComponentProperties
    {
        $component = $this->getComponentName( $get );

        if ( ! $component ) {
            return null;
        }

        return $this->propertiesCache[$component] ??= ComponentProperties::from( $this->components[$component] );
    }

    public function hasTag( string $tag ) : bool
    {
        return \array_key_exists( $tag, $this->tags );
    }

    /**
     * Begin the Build proccess of a component.
     *
     * @template T
     *
     * @param class-string<T>|string $component
     *
     * @return ComponentInterface|T
     */
    public function build( string $component ) : mixed
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
        $taggedProperties = $this->components[$component]['taggedProperties'];

        $tag = $arguments['tag'] ?? null;

        if ( isset( $arguments['tag'] ) ) {
            $this->parseTaggedAttributes( $arguments, $taggedProperties );
        }

        dump( $arguments, $taggedProperties );

        // if ( $properties['taggedProperties'] && isset( $arguments['tag'] ) ) {
        //     $tags  = \explode( ':', $arguments['tag'] );
        //     $props = $properties['taggedProperties'][$tags[0]] ?? null;
        //
        //     foreach ( $tags as $position => $tag ) {
        //         if ( $props[$position] ) {
        //             $arguments[$props[$position]] = $tag;
        //         }
        //     }
        //     dump( $arguments, $properties['taggedProperties'] );
        // }

        $component = $this->build( $component );
        $component->build( $arguments );

        dump( $component );
        return $component->render();

        if ( ! $render ) {
            Log::exception( new ComponentNotFoundException( $component ), Level::CRITICAL );
            return '';
        }

        \assert( \is_subclass_of( $render['class'], ComponentInterface::class ) );

        // foreach ( $render['autowire'] as $argument => $class ) {
        //     $render['autowire'][$argument] = $this->serviceLocator( $class, true );
        // }

        $uniqueId = null;

        $create = $render['class']::compile(
            $arguments,
            [],
            $uniqueId,
            $this->logger,
        );

        $this->instantiated[$component][] = $create->componentUniqueId();
        return $create->render() ?? '';
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
        // If the provided value matches an array name, return it
        if ( \array_key_exists( $value, $this->components ) ) {
            return $value;
        }

        // If the value is a class-string, the class exists, and is a component, return the name
        if ( \str_contains( $value, '\\' ) && \class_exists( $value ) ) {
            $component = Arr::search( $this->components, $value );
        }

        // Check if the $value matches a tag
        $component = $this->tags[$value] ?? null;

        if ( ! $component ) {
            return null;
        }

        if ( \is_array( $component ) ) {
            $component = \end( $component );
        }

        return $component;
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
