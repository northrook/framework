<?php

declare(strict_types=1);

namespace Core\View;

use Core\Framework\DependencyInjection\ServiceContainer;
use Core\View\Exception\ComponentNotFoundException;
use Core\View\Render\ComponentInterface;
use Northrook\Logger\{Level, Log};
use Psr\Log\LoggerInterface;
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
     * @param array<class-string, array{name: string, class:class-string, tags: string[], autowire: class-string[]}> $components
     * @param array                                                                                                  $tags
     * @param ?LoggerInterface                                                                                       $logger
     */
    public function __construct(
        private readonly array            $components = [],
        private readonly array            $tags = [],
        private readonly ?LoggerInterface $logger = null,
    ) {
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
    public function render(
        string $component,
        array  $arguments = [],
        ?int   $cache = AUTO,
    ) : string {
        $render = $this->components[$component] ?? null;

        if ( ! $render ) {
            Log::exception( new ComponentNotFoundException( $component ), Level::CRITICAL );
            return '';
        }

        \assert( \is_subclass_of( $render['class'], ComponentInterface::class ) );

        foreach ( $render['autowire'] as $argument => $class ) {
            $render['autowire'][$argument] = $this->serviceLocator( $class, true );
        }

        $uniqueId = null;

        $create = $render['class']::create(
            $arguments,
            $render['autowire'],
            $uniqueId,
            $this->logger,
        );

        $this->instantiated[$component][] = $create->componentUniqueId();
        return $create->render() ?? '';
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

    public function getByTag( string $tag ) : array
    {
        $component = $this->tags[$tag] ?? null;

        if ( ! $component ) {
            throw new ComponentNotFoundException( $tag );
        }

        return $this->components[$component];
    }

    public function hasTag( string $tag ) : bool
    {
        return \array_key_exists( $tag, $this->tags );
    }
}
