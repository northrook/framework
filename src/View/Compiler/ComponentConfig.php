<?php

declare(strict_types=1);

namespace Core\View\Compiler;

use Core\View\Attribute\ComponentNode;
use Core\View\Render\ComponentInterface;
use Exception\NotImplementedException;
use Support\{ClassInfo, Reflect};
use JetBrains\PhpStorm\ExpectedValues;
use ReflectionNamedType;

final readonly class ComponentConfig
{
    public string $name;

    public string $class;

    public array $tags;

    public array $autowire;

    #[ExpectedValues( values : ['live', 'static', 'runtime'] )]
    public string $type;

    public bool $allowChildren;

    /**
     * @param array{
     *     name: string,
     *     class: class-string,
     *     tags: string[],
     *     type: string,
     *     allowChildren: bool,
     *     autowire: array<string, class-string>
     *     }  $config
     */
    public function __construct( array $config )
    {
        foreach ( $config as $property => $value ) {
            $this->{$property} = $value;
        }
        // $this->name          = $config['name'];
        // $this->class         = $config['class'];
        // $this->tags          = $config['tags'];
        // $this->type          = $config['type'];
        // $this->autowire      = $config['autowire'];
        // $this->allowChildren = $config['allowChildren'];
        //
        // unset( $config );
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

        $compilerNode ??= new ComponentNode();

        return [
            'name'          => $component->class::componentName(),
            'class'         => $component->class,
            'tags'          => $compilerNode->tags,
            'type'          => $compilerNode->type,
            'allowChildren' => $compilerNode->allowChildren,
            'autowire'      => $autowire,
        ];
    }
}
