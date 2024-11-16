<?php

declare(strict_types=1);

namespace Core\View\Compiler;

use Core\View\Attribute\ComponentNode;
use Core\View\Render\ComponentInterface;
use Exception\NotImplementedException;
use Support\{ClassInfo, Reflect};
use ReflectionNamedType;

final readonly class ComponentConfig
{
    public string $name;

    public string $class;

    public array $tags;

    public array $autowire;

    public bool $isStatic;

    public bool $allowChildren;

    /**
     * @param array{name: string, class: class-string, tags: string[], config: array<string, bool|int|string>, autowire: array<string, class-string>} $config
     */
    public function __construct( array $config )
    {
        $this->name          = $config['name'];
        $this->class         = $config['class'];
        $this->tags          = $config['tags'];
        $this->autowire      = $config['autowire'];
        $this->isStatic      = $config['config']['static']         ?? false;
        $this->allowChildren = $config['config']['allow_children'] ?? true;

        unset( $config );
    }

    public static function compile( string|ClassInfo|ComponentInterface $component ) : array
    {
        if ( ! $component instanceof ComponentInterface ) {
            $component = new ClassInfo( $component );
        }

        if ( ! \is_subclass_of( $component->class, ComponentInterface::class ) ) {
            throw new NotImplementedException( $component->class, ComponentInterface::class );
        }

        $autowire = [];

        foreach ( $component->reflect()->getConstructor()->getParameters() as $param ) {
            if ( ! $param->getType() instanceof ReflectionNamedType ) {
                continue;
            }

            $typeHint = $param->getType()->getName();
            if ( \class_exists( $typeHint ) ) {
                $autowire[$param->getName()] = $typeHint;
            }
        }

        $compilerNode = Reflect::getAttribute( $component->reflect(), ComponentNode::class );

        return [
            'name'   => $component->class::componentName(),
            'class'  => $component->class,
            'tags'   => $compilerNode->tags ?? [],
            'config' => [
                'static'        => $compilerNode?->static        ?? false,
                'allowChildren' => $compilerNode?->allowChildren ?? true,
            ],
            'autowire' => $autowire,
        ];
    }
}
