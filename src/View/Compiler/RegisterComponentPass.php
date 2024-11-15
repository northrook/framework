<?php

namespace Core\View\Compiler;

use Core\Symfony\DependencyInjection\CompilerPass;
use Core\View\{Attribute\ComponentNode, ComponentFactory, Render\ComponentInterface};
use Exception\NotImplementedException;
use ReflectionNamedType;
use Support\{ClassInfo, Reflect};
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @
 */
abstract class RegisterComponentPass extends CompilerPass
{
    abstract public function register() : array;

    final public function compile( ContainerBuilder $container ) : void
    {
        $componentFactory = $container->getDefinition( ComponentFactory::class );

        $components = [];
        $matchTags  = [];

        foreach ( $this->register() as $component ) {
            [$name, $class, $tags, $autowire] = $this->parse( $component );

            $components[$name] = [
                'name'     => $name,
                'class'    => $class,
                'tags'     => $tags,
                'autowire' => $autowire,
            ];

            foreach ( $tags as $tag ) {
                $matchTags[$tag] = $name;
            }
        }

        $componentFactory->setArguments( [$components, $matchTags] );
    }

    private function parse( string|ClassInfo|ComponentInterface $register ) : array
    {
        if ( ! $register instanceof ComponentInterface ) {
            $register = new ClassInfo( $register );
        }

        if ( ! $register->implements( ComponentInterface::class ) ) {
            throw new NotImplementedException( $register->class, ComponentInterface::class );
        }

        $autowire = [];

        foreach ( $register->reflect()->getConstructor()->getParameters() as $param ) {
            if ( ! $param->getType() instanceof ReflectionNamedType ) {
                continue;
            }

            $typeHint = $param->getType()->getName();
            if ( \class_exists( $typeHint ) ) {
                $autowire[$param->getName()] = $param->getType()->getName();
            }
        }

        $componentNode = Reflect::getAttribute( $register->reflect(), ComponentNode::class );

        /** @type class-string<\Core\View\Render\ComponentInterface> $register */
        return [
            $register->class::componentName(),
            $register->class,
            $componentNode->tags ?? [],
            $autowire,
        ];
    }
}
