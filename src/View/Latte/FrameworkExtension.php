<?php

declare(strict_types=1);

namespace Core\View\Latte;

use Core\Framework\Autowire\UrlGenerator;
use Core\View\{ComponentFactory, Component\ComponentInterface};
use Core\View\Latte\Node\InlineStringableNode;
use Core\View\Template\Compiler\NodeCompiler;
use Latte\Compiler\{Node, Nodes\AuxiliaryNode, NodeTraverser};
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

    public function __construct( public readonly ComponentFactory $factory )
    {
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
        dump( $this->factory );
        return [
            'static_component_pass'  => [$this, 'staticComponentCompilerPass'],
            'runtime_component_pass' => [$this, 'runtimeComponentCompilerPass'],
        ];
    }

    public function staticComponentCompilerPass( TemplateNode $template ) : void
    {
        dump( __METHOD__ );
        ( new NodeTraverser() )->traverse(
            $template,
            function( Node $node ) : int|Node {
                if ( $node instanceof ExpressionNode ) {
                    return NodeTraverser::DontTraverseChildren;
                }

                if ( ! $node instanceof ElementNode ) {
                    return $node;
                }

                $tag = $this->nodeTag( $node );

                dump( $tag );

                return $node;
            },
        );
    }

    public function runtimeComponentCompilerPass( TemplateNode $template ) : void
    {
        dump( __METHOD__ );
        ( new NodeTraverser() )->traverse(
            $template,
            function( Node $node ) : int|Node {
                if ( $node instanceof ExpressionNode ) {
                    return NodeTraverser::DontTraverseChildren;
                }

                if ( ! $node instanceof ElementNode ) {
                    return $node;
                }

                $tag = $this->nodeTag( $node );

                // TODO :: handle blind call to ui:{component}
                if ( \str_starts_with( $node->name, 'ui:' ) ) {
                    dump( $node->name.'::'.$tag );
                }
                else {
                    dump( $tag );
                }

                return $node;
            },
        );
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

        $tag = $this->nodeTag( $node );

        $component = $this->factory->getComponentName( $tag );

        if ( ! $component ) {
            return $node;
        }
        $component = $this->factory->build( $component );

        $html = $this->factory->render(
            $component::componentName(),
            $component::nodeArguments( new NodeCompiler( $node ) ),
        );
        return new AuxiliaryNode( static fn() => "echo '{$html}';" );

        $component->build( $component->nodeArguments( new NodeCompiler( $node ) ) );

        dump( $component, $component->render() );

        // dump($component->build( $component->nodeArguments( new NodeCompiler( $node ) ) )->render());

        // return $component->templateNode( new NodeCompiler( $node ) );
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

    private function nodeTag( ElementNode $node ) : string
    {
        return \strstr( $node->name, ':', true ) ?: $node->name;
    }

    #[Override]
    public function getProviders() : array
    {
        return [
            'component' => $this->factory,
        ];
    }

    public function traverseTemplateNodes( TemplateNode $templateNode ) : void
    {
        ( new NodeTraverser() )->traverse( $templateNode, [$this, 'parseTemplate'] );
    }

    /**
     * @param Node $node
     *
     * @return false|int|Node
     */
    private function skip( Node $node ) : false|int|Node
    {
        if ( $node instanceof ExpressionNode ) {
            return NodeTraverser::DontTraverseChildren;
        }

        if ( ! $node instanceof ElementNode ) {
            return $node;
        }

        return false;
    }
}
