<?php

namespace Core\View\Compiler;

use Core\Symfony\DependencyInjection\CompilerPass;
use Core\View\{Attribute\ComponentNode, ComponentFactory, ComponentInterface};
use Exception\NotImplementedException;
use Support\{ClassInfo, Reflect};
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class RegisterComponentPass extends CompilerPass
{
    abstract public function register() : array;

    final public function compile( ContainerBuilder $container ) : void
    {
        $componentFactory = $container->getDefinition( ComponentFactory::class );

        $components = [];
        $tags       = [];

        foreach ( $this->register() as $component ) {
            [$name, $class, $tags, $autowire] = $this->parse( $component );

            $components[$name] = [
                'name'     => $name,
                'class'    => $class,
                'tags'     => $tags,
                'autowire' => $autowire,
            ];

            foreach ( $component['tags'] as $tag ) {
                $tags[$tag] = $class;
            }
        }

        $this->console->info( $this::class.' Registered '.\count( $components ).' components.' );
        $componentFactory->setArguments( [$components, $tags] );
    }

    private function parse( string|ClassInfo|ComponentInterface $register ) : array
    {
        if ( ! $register instanceof ComponentInterface ) {
            $register = new ClassInfo( $register );
        }

        if ( ! $register->implements( ComponentInterface::class ) ) {
            throw new NotImplementedException( $register->class, ComponentInterface::class );
        }

        $componentNode = Reflect::getAttribute( $register->reflect(), ComponentNode::class );

        $constructor = $register->reflect()->getConstructor();
        dump($constructor);

        /** @type class-string<ComponentInterface> $register */
        return [
            $register->class::componentName(),
            $register->class,
            $componentNode->tags ?? [],
        ];
    }
}
