<?php

namespace Core\UI\Component;

use Core\View\Attribute\ComponentNode;
use Core\View\Component\ComponentBuilder;
use Core\View\IconRenderer;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;

#[ComponentNode( ['button', 'button:submit'], 'static' )]
final class Button extends ComponentBuilder
{
    protected const ?string TAG = 'button';

    protected ?string $icon = null;

    public function __construct( private readonly IconRenderer $iconRenderer )
    {
    }

    protected function parseArguments( array &$arguments ) : void
    {
        $this->icon = $arguments['icon'] ?? null;
        unset( $arguments['icon'] );
    }

    protected function compile() : string
    {
        if ( $this->icon && $this->icon = $this->iconRenderer->iconPack->get( $this->icon ) ) {
            $this->component->content( $this->icon, true );
        }

        return (string) $this->component;
    }

    protected function submit() : void
    {
        $this->component->attributes->set( 'type', 'submit' );
    }

    public function templateNode( NodeCompiler $node ) : AuxiliaryNode
    {
        return Render::templateNode(
            self::componentName(),
            $this::nodeArguments( $node ),
        );
    }

    public static function nodeArguments( NodeCompiler $node ) : array
    {
        return [
            'tag'        => $node->tag,
            'attributes' => $node->attributes(),
            'content'    => $node->parseContent(),
        ];
    }
}
