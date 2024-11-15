<?php

namespace Core\UI\Component;

use Core\UI\Component;
use Core\View\Render\ComponentInterface;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Psr\Log\LoggerInterface;

final class Heading extends Component
{
    protected function build() : string
    {
        return (string) $this->element;
    }

    public static function create(
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

        return Render::auxiliaryNode(
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
