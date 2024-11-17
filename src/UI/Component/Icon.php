<?php

namespace Core\UI\Component;

use Core\View\Attribute\ComponentNode;
use Core\View\Component\{ComponentBuilder};
use Core\View\IconRenderer;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;

#[ComponentNode( 'icon:{get}', 'static' )]
final class Icon extends ComponentBuilder
{
    protected const string TAG = 'i';

    protected string $get;

    public function __construct( private readonly IconRenderer $icon )
    {
    }

    protected function compile() : string
    {
        $this->element->content( 'hello there, Icon Man!' );
        return (string) $this->element;
    }

    public static function templateNode( NodeCompiler $node ) : AuxiliaryNode
    {
        $attributes = $node->attributes();

        $href = $node->arguments()['href'] ?? $attributes['href'] ?? null;

        unset( $attributes['href'] );

        return Render::auxiliaryNode(
            self::componentName(),
            [
                'get'       => $href,
                'attributes' => $node->attributes(),
                'content'    => $node->parseContent(),
                'tag'        => $node->tag,
            ],
        );
    }
}
