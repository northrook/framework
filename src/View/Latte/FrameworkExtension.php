<?php

declare(strict_types=1);

namespace Core\View\Latte;

use Core\Framework\Autowire\UrlGenerator;
use Core\View\{Attribute\ComponentNode, ComponentFactory, Component\ComponentInterface};
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
        dump( __METHOD__ );
        return [
            // 'static_components'         => [$this, 'earlyCompilerPass'],
            // 'after::static_components'  => self::order( [$this, 'afterEarlyCompilerPass'], after: '*' ),
            // 'before::static_components' => self::order( [$this, 'beforeEarlyCompilerPass'], before: '*' ),
            self::class => [$this, 'traverseTemplateNodes'],
        ];
    }

    public function earlyCompilerPass( TemplateNode $templateNode ) : void
    {
        dump( __METHOD__, $templateNode, '---' );
    }

    public function beforeEarlyCompilerPass( TemplateNode $templateNode ) : void
    {
        dump( __METHOD__, $templateNode, '---' );
    }

    public function afterEarlyCompilerPass( TemplateNode $templateNode ) : void
    {
        dump( __METHOD__, $templateNode, '---' );
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

        // TODO :: handle blind call to ui:{component}
        if ( \str_starts_with( $node->name, 'ui:' ) ) {
            dump( $node->name );
        }

        [$tag, $arg] = $this->nodeTag( $node );

        $component = $this->factory->getComponentName( $tag);

        if ( ! $component ) {
            return $node;
        }

        $component = $this->factory->build( $component );
        //
        // if ( 'runtime' === $properties['render'] ) {
        //     return $component->class::templateNode( new NodeCompiler( $node ) );
        // }

        dump( $component );

        return  $component::templateNode( new NodeCompiler( $node ) );

        // if ( $this->factory->hasTag( $node->name ) ) {
        //     $parse = $this->factory->getByTag( $node->name );
        //
        //     \assert( \is_subclass_of( $parse['class'], ComponentInterface::class ) );
        //
        //     $compiler = new NodeCompiler( $node );
        //
        //     dump( $this->factory->getComponentConfig( $node->name ) );
        //
        //     return $parse['class']::templateNode( $compiler );
        // }

        return $node;
    }

    private function nodeTag( ElementNode $node ) : array
    {
        $tag = $node->name;
        $arg = null;

        if ( \str_contains( $tag, ':' ) ) {
            [$tag, $arg] = \explode( ':', $tag );
            $tag .= ':';
        }

        return [$tag, $arg];
    }

    #[Override]
    public function getProviders() : array
    {
        return [
            'component' => $this->factory,
        ];
    }
}
