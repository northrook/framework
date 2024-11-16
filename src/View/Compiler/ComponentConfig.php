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

    /** @var array<string, string> */
    public array $parameters;

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
     *     parameters: array<string, string>
     *     }  $config
     */
    public function __construct( array $config )
    {
        foreach ( $config as $property => $value ) {
            $this->{$property} = $value;
        }
    }

    public static function compile( string|ClassInfo|ComponentInterface $component ) : array
    {
        if ( ! $component instanceof ComponentInterface ) {
            $component = new ClassInfo( $component );
        }

        if ( ! \is_subclass_of( $component->class, ComponentInterface::class ) ) {
            throw new NotImplementedException( $component->class, ComponentInterface::class );
        }

        $parameters = [];

        foreach ( $component->reflect()->getConstructor()->getParameters() as $index => $param ) {
            if ( ! $param->getType() instanceof ReflectionNamedType ) {
                continue;
            }

            $parameter = $param->getName();

            $typeHint = $param->allowsNull() ? '?' : '';

            $typeHint .= $param->getType()->getName();

            $parameters[$parameter] = $typeHint;
        }

        $compilerNode = Reflect::getAttribute( $component->reflect(), ComponentNode::class );

        $compilerNode ??= new ComponentNode();

        return [
            'name'          => $component->class::componentName(),
            'class'         => $component->class,
            'tags'          => $compilerNode->tags,
            'type'          => $compilerNode->type,
            'allowChildren' => $compilerNode->allowChildren,
            'parameters'    => $parameters,
        ];
    }
}
