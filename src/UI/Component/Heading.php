<?php

namespace Core\UI\Component;

use Core\View\Attribute\ComponentNode;
use Core\View\Component\ComponentBuilder;
use Core\View\IconRenderer;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Northrook\HTML\Element\Tag;

#[ComponentNode( Tag::HEADING, 'static' )]
final class Heading extends ComponentBuilder
{
    protected function compile() : string
    {
        // $this->component->content( $this->icon->iconPack->get( $this->get ) );
        return (string) $this->component;
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
