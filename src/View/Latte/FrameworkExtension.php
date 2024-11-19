<?php

declare(strict_types=1);

namespace Core\View\Latte;

use Core\Framework\Autowire\UrlGenerator;
use Core\View\{ComponentFactory};
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
        return [
            'early_component_pass' => fn( TemplateNode $template ) => $this->componentCompilerPass(
                $template,
                'static',
            ),
            'static_component_pass'  => [$this, 'staticComponentCompilerPass'],
            'runtime_component_pass' => [$this, 'runtimeComponentCompilerPass'],
        ];
    }

    /**
     * @param TemplateNode              $template
     * @param 'live'|'runtime'|'static' $render
     */
    public function componentCompilerPass( TemplateNode $template, string $render ) : void
    {
        ( new NodeTraverser() )->traverse(
            $template,
            function( Node $node ) use ( $render ) : int|Node {
                if ( $node instanceof ExpressionNode ) {
                    return NodeTraverser::DontTraverseChildren;
                }

                if ( ! $node instanceof ElementNode ) {
                    return $node;
                }

                dump( $render );

                return $node;
            },
        );
    }

    public function staticComponentCompilerPass( TemplateNode $template ) : void
    {
        dump( $template );
        ( new NodeTraverser() )->traverse(
            $template,
            function( Node $node ) : int|Node {
                if ( $node instanceof ExpressionNode ) {
                    return NodeTraverser::DontTraverseChildren;
                }

                if ( ! $node instanceof ElementNode ) {
                    return $node;
                }

                $component = $this->factory->getComponentProperties( $node->name );

                if ( ! $component ) {
                    return $node;
                }

                dump( $component );

                if ( 'static' !== $component->render ) {
                    return $node;
                }

                $component = $this->factory->build( $component );

                $html = $this->factory->render(
                    $component::componentName(),
                    $component::nodeArguments( new NodeCompiler( $node ) ),
                );
                return new AuxiliaryNode( static fn() => "echo '{$html}';" );
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
                return $node;

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
}
