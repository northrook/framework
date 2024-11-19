<?php

namespace Core\UI\Component;

use Core\View\Attribute\ComponentNode;
use Core\View\Component\ComponentBuilder;
use Core\View\Render\HtmlContent;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;

#[ComponentNode( ['pre', 'code:{lang}:block'], 'static' )]
final class Code extends ComponentBuilder
{
    protected const ?string TAG = 'code';

    protected bool $tidy = false;

    protected ?string $lang = null;

    protected ?string $block = null;

    private string $code;

    protected function parseArguments( array &$arguments ) : void
    {
        $this->code = $arguments['content'] ?? null;

        unset( $arguments['content'] );
    }

    protected function compile() : string
    {
        $this->component->content( HtmlContent::contentArray( $this->code ) );
        dump( $this );
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
