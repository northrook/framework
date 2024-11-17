<?php

declare(strict_types=1);

namespace Core\View\Compiler;

use Core\View\Attribute\ComponentNode;
use Core\View\Component\ComponentInterface;
use Exception\NotImplementedException;
use Support\{ClassInfo, Reflect};
use JetBrains\PhpStorm\ExpectedValues;

/**
 * @internal
 * @used-by \Core\View\Compiler\RegisterComponentPass
 */
final readonly class ComponentParser
{
    /** @var non-empty-lowercase-string */
    public string $name;

    /** @var class-string<\Core\View\Component\ComponentInterface> */
    public string $class;

    public ClassInfo $component;

    /** @var array<int, string> */
    public array $tags;

    #[ExpectedValues( values : ['live', 'static', 'runtime'] )]
    public string $type;

    public function __construct( string|ClassInfo $component )
    {
        $this->parse( $component );
        $this->validateComponent();

        $this->class = $this->component->class;

        $this->name = $this->class::componentName();

        $node = $this->nodeAttribute();

        $this->type = $node->type;
        $this->tags = $node->tags;
    }

    private function parse( string|ClassInfo &$component ) : void
    {
        $this->component = $component instanceof ClassInfo ? $component : new ClassInfo( $component );
        unset( $component );
    }

    private function validateComponent() : void
    {
        if ( ! \is_subclass_of( $this->component->class, ComponentInterface::class ) ) {
            throw new NotImplementedException( $this->component->class, ComponentInterface::class );
        }
    }

    private function nodeAttribute() : ComponentNode
    {
        return Reflect::getAttribute( $this->component->reflect(), ComponentNode::class ) ?? new ComponentNode();
    }
}
