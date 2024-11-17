<?php

namespace Core\View\Compiler;

use Core\Symfony\DependencyInjection\CompilerPass;
use Core\View\{Attribute\ComponentNode, ComponentFactory, Component\ComponentInterface};
use Exception\NotImplementedException;
use ReflectionNamedType;
use Support\{ClassInfo, Reflect};
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 */
abstract class RegisterComponentPass extends CompilerPass
{
    private array $components = [];

    private array $tags;

    abstract public function register() : array;

    final public function compile( ContainerBuilder $container ) : void
    {
        $componentFactory = $container->getDefinition( ComponentFactory::class );
        $componentLocator = $container->getDefinition( 'core.component_locator' );

        $components = [];
        $matchTags  = [];

        foreach ( $this->register() as $component ) {
            $register = new ComponentParser( $component );

            dump( $register );

            $component = ComponentBuilder::config( $component );

            $components[$component['name']] = $component;

            foreach ( $component['tags'] as $tag ) {
                if ( ! $tag || \preg_match( '#[^a-z]#', $tag[0] ) ) {
                    $reason = $tag ? null : 'Tags cannot be empty.';
                    $reason ??= ':' === $tag[0] ? 'Tags cannot start with a separator.'
                            : 'Tags must start with a letter.';
                    $this->console->error( ['Invalid component tag.', 'Value: '.$tag, $reason] );

                    continue;
                }

                $subtype = \strpos( $tag, ':' );

                if ( $subtype ) {
                    [$tag, $subtype]                = \explode( ':', $tag );
                    $matchTags["{$tag}:"][$subtype] = $component['name'];
                }
                else {
                    $matchTags[$tag] = $component['name'];
                }
            }
        }

        $componentFactory->replaceArgument( 0, $components );
        $componentFactory->replaceArgument( 1, $matchTags );
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

        /** @type class-string<\Core\View\Component\ComponentInterface> $register */
        return [
            $register->class::componentName(),
            $register->class,
            $componentNode->tags ?? [],
            $autowire,
        ];
    }
}
