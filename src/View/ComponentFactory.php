<?php

declare(strict_types=1);

namespace Core\View;

use Core\Framework\DependencyInjection\ServiceContainer;
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

    /**
     * `[ className => componentName ]`.
     *
     * @var array<class-string|string, array<int, string>>
     */
    private array $instantiated = [];

    /**
     * Provide a [class-string, args[]] array.
     *
     * @param array<class-string, array{render: 'live'|'runtime'|'static', tagged: array<array-key,mixed>} > $components
     * @param array                                                                                          $tags
     * @param ServiceLocator                                                                                 $componentLocator
     */
    public function __construct(
        private readonly array          $components,
        private readonly array          $tags,
        private readonly ServiceLocator $componentLocator,
    ) {
    }

    /**
     * Begin the Build proccess of a component.
     *
     * @param string $component
     *
     * @return ComponentInterface
     */
    public function build( string $component ) : ComponentInterface
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
        $component = $this->build( $component );

        $component->build( $arguments );
        dump( $component );
        return '';

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
     * @param string                         $get
     * @param null|'live'|'runtime'|'static' $type
     *
     * @return null|string
     */
    public function getComponentName( string $get, ?string $type = null ) : ?string
    {
        if ( \str_contains( $get, '\\' ) && \class_exists( $get ) ) {
            $component = Arr::search( $this->components, $get );
        }

        $component = $this->tags[$get] ?? null;

        if ( ! $component ) {
            return null;
        }

        if ( \is_array( $component ) ) {
            $component = \end( $component );
        }

        if ( $type && $type !== $this->components[$component]['render'] ) {
            return null;
        }

        return $component;
    }

    public function hasComponent( string $component ) : bool
    {
        return \array_key_exists( $component, $this->components );
    }

    public function hasTag( string $tag ) : bool
    {
        return \array_key_exists( $tag, $this->tags );
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
