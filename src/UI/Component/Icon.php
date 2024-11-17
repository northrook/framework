<?php

namespace Core\UI\Component;

use Core\View\Attribute\ComponentNode;
use Core\View\Component\{ComponentBuilder, ComponentInterface};
use Core\View\IconRenderer;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Psr\Log\LoggerInterface;
use ValueError;

#[ComponentNode( 'icon:{get}', 'static' )]
final class Icon extends ComponentBuilder
{
    public function __construct(
        private readonly IconRenderer $icon,
    ) {
    }

    protected function build( string $get ) : string
    {
        return (string) $this->element;
    }

    public static function create(
        array            $arguments,
        array            $autowire = [],
        ?string          $uniqueId = null,
        ?LoggerInterface $logger = null,
    ) : ComponentInterface {
        $get        = $arguments['get']        ?? throw new ValueError( 'No [icon get] value is provided.' );
        $attributes = $arguments['attributes'] ?? [];
        $content    = $arguments['content']    ?? '';
        $tag        = $arguments['tag']        ?? 'a';
        return new self( 'i' );
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
