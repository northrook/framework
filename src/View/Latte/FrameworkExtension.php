<?php

declare(strict_types=1);

namespace Core\View\Latte;

use Core\Framework\Autowire\UrlGenerator;
use Core\View\{ComponentFactory, ComponentInterface};
use Core\View\Latte\Node\InlineStringableNode;
use Core\View\Template\Compiler\NodeCompiler;
use Latte\Compiler\{Node, NodeTraverser};
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\TemplateNode;
use Latte\Extension as LatteExtension;
use Override;

final class FrameworkExtension extends LatteExtension
{
    // use NodeCompilerMethods, UrlGenerator;
    use UrlGenerator;

    private array $registetedTags = [];

    public function __construct(
        public readonly ComponentFactory $factory,
    ) {
        dump( $this->factory );
    }

    public function getTags() : array
    {
        return [
            'inline' => [InlineStringableNode::class, 'create'],
        ];
    }

    public function getFunctions() : array
    {
        return [
            'url'  => $this->generateRouteUrl( ... ),
            'path' => $this->generateRoutePath( ... ),
        ];
    }

    #[Override]
    public function getPasses() : array
    {
        return [
            self::class => [$this, 'traverseTemplateNodes'],
        ];
    }

    public function traverseTemplateNodes( TemplateNode $templateNode ) : void
    {
        ( new NodeTraverser() )->traverse( $templateNode, [$this, 'parseTemplate'] );
    }

    public function parseTemplate( Node $node ) : int|Node
    {
        if ( $node instanceof ExpressionNode ) {
            return NodeTraverser::DontTraverseChildren;
        }

        if ( ! $node instanceof ElementNode ) {
            return $node;
        }

        if ( $this->factory->hasTag( $node->name ) ) {
            $parse = $this->factory->getByTag( $node->name );

            \assert( \is_subclass_of( $parse['class'], ComponentInterface::class ) );

            $compiler = new NodeCompiler( $node );

            return $parse['class']::templateNode( $compiler );
        }

        return $node;
    }

    #[Override]
    public function getProviders() : array
    {
        return [
            'component' => $this->factory,
        ];
    }
}
