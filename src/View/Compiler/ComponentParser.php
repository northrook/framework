<?php

declare(strict_types=1);

namespace Core\View\Compiler;

use Core\Symfony\Console\Output;
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
    private ClassInfo $component;

    private ComponentNode $componentNode;

    /** @var non-empty-lowercase-string */
    public string $name;

    /** @var class-string<\Core\View\Component\ComponentInterface> */
    public string $class;

    /** @var array<int, string> */
    public array $tags;

    #[ExpectedValues( values : ['live', 'static', 'runtime'] )]
    public string $render;

    public function __construct( string|ClassInfo $component )
    {
        $this->parse( $component );
        $this->validateComponent();
        $this->nodeAttribute();

        $this->class = $this->component->class;

        $this->name = $this->class::componentName();

        $this->tags = $this->componentNodeTags();
    }

    protected function componentNodeTags() : array
    {
        $set = [];

        foreach ( $this->componentNode->tags as $tag ) {
            if ( ! $tag || \preg_match( '#[^a-z]#', $tag[0] ) ) {
                $reason = $tag ? null : 'Tags cannot be empty.';
                $reason ??= ':' === $tag[0] ? 'Tags cannot start with a separator.'
                        : 'Tags must start with a letter.';
                Output::error( ['Invalid component tag.', 'Value: '.$tag, $reason] );

                continue;
            }

            if ( \str_contains( $tag, ':' ) ) {
                [$tag, $subtype]          = \explode( ':', $tag );
                $set["{$tag}:"][$subtype] = $this->name;
            }
            else {
                $set[$tag] = $this->name;
            }
        }

        return $set;
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

    private function nodeAttribute() : void
    {
        $this->componentNode
                = Reflect::getAttribute( $this->component->reflect(), ComponentNode::class )
                  ?? new ComponentNode();
    }
}
