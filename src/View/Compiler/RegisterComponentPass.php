<?php

namespace Core\View\Compiler;

use Core\View\ComponentFactory;
use Core\View\ComponentFactory\ComponentParser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Core\Symfony\DependencyInjection\CompilerPass;

abstract class RegisterComponentPass extends CompilerPass
{
    private array $components = [];

    private array $tags = [];

    abstract public function register() : array;

    final public function compile( ContainerBuilder $container ) : void
    {
        $componentFactory = $container->getDefinition( ComponentFactory::class );
        $componentLocator = $container->getDefinition( 'core.component_locator' );

        $components = [];

        $priority = [];

        foreach ( $this->register() as $component ) {
            $register = new ComponentParser( $component );

            $this->tags = \array_merge( $this->tags, $register->tags );

            $definition = $container->register( "component.{$register->name}", $register->class );
            $definition->setAutowired( true );

            $this->components[$register->name] = $register->properties;

            $components[$register->name] = $definition;
        }

        dump( $components );

        $componentLocator->setArguments( [$components] );

        $componentFactory->replaceArgument( 0, $this->components );
        $componentFactory->replaceArgument( 1, $this->tags );
    }
}
