<?php

namespace Core\UI\Component;

use Core\UI\Component;
use Core\View\Attribute\ComponentNode;
use Core\View\Component\ComponentInterface;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Northrook\HTML\Element\Tag;
use Psr\Log\LoggerInterface;

#[ComponentNode( Tag::HEADING )]
final class Heading extends Component
{
    protected function compile() : string
    {
        return (string) $this->element;
    }

    public static function build(
        array            $arguments,
        array            $autowire = [],
        ?string          $uniqueId = null,
        ?LoggerInterface $logger = null,
    ) : ComponentInterface {
        return new self( 'h1' );
    }

    public static function templateNode( NodeCompiler $node ) : AuxiliaryNode
    {
        $attributes = $node->attributes();

        $href = $node->arguments()['href'] ?? $attributes['href'] ?? null;

        unset( $attributes['href'] );

        return Render::templateNode(
            self::componentName(),
            [
                'href'       => $href,
                'attributes' => $node->attributes(),
                'content'    => $node->parseContent(),
                'tag'        => $node->tag,
            ],
        );
    }
}
