<?php

namespace Core\View\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Core\Symfony\DependencyInjection\CompilerPass;
use Core\View\ComponentFactory;

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

        foreach ( $this->register() as $component ) {
            $register = new ComponentParser( $component );

            $this->tags = \array_merge( $this->tags, $register->tags );

            $definition = $container->register( "component.{$register->name}", $register->class );
            $definition->setAutowired( true );

            $this->components[$register->name] = (array) $register->properties;

            $components[$register->name] = $definition;
        }

        $componentLocator->setArguments( [$components] );

        $componentFactory->replaceArgument( 0, $this->components );
        $componentFactory->replaceArgument( 1, $this->tags );
    }
}
