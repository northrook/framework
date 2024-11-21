<?php

namespace Core\UI\Component;

use Core\UI\Attribute\TemplateNode;
use Core\View\Component\ComponentBuilder;
use Core\View\IconRenderer;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;

#[TemplateNode( 'icon:{get}', 'static', 128 )]
final class Icon extends ComponentBuilder
{
    protected const ?string TAG = 'i';

    protected string $get;

    public function __construct( private readonly IconRenderer $icon )
    {
    }

    protected function compile() : string
    {
        $this->component->content( $this->icon->iconPack->get( $this->get ) );
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
