<?php

declare(strict_types=1);

namespace Core\View\Latte;

use Core\Framework\Autowire\UrlGenerator;
use Core\View\Component\ComponentNode;
use Core\View\ComponentFactory;
use Core\View\Latte\Node\InlineStringableNode;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Node\StaticNode;
use Core\View\Template\TemplateCompiler;
use Latte\Compiler\{Node, Nodes\TextNode, NodeTraverser};
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\TemplateNode;
use Latte\Extension as LatteExtension;
use Override;

final class FrameworkExtension extends LatteExtension
{
    // use NodeCompilerMethods, UrlGenerator;
    use UrlGenerator;

    private array $staticComponents;

    private array $nodeComponents;

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
        $static     = [];
        $components = [];

        foreach ( $this->factory->getRegisteredComponents() as $component ) {
            $component = new ComponentFactory\ComponentProperties( ...$component );

            if ( $component->static ) {
                $index = $component->priority ?: \count( $static );
                if ( \array_key_exists( $component->priority, $static ) ) {
                    $index++;
                }
                $static[$index] = $component;
            }
            else {
                $index = $component->priority ?: \count( $components );
                if ( \array_key_exists( $component->priority, $components ) ) {
                    $index++;
                }
                $components[$index] = $component;
            }
        }
        \ksort( $static );
        \ksort( $components );

        foreach ( \array_reverse( $static ) as $component ) {
            $this->staticComponents[$component->name] = $component;
        }

        foreach ( \array_reverse( $components ) as $component ) {
            $this->nodeComponents[$component->name] = $component;
        }

        // dump( $this );

        $componentPasses = [];

        foreach ( $this->staticComponents as $name => $component ) {
            $componentPasses["static-{$component->name}-pass"] = fn( TemplateNode $template ) => $this->componentPass(
                $template,
                $component,
            );
        }

        dump( $componentPasses );

        return $componentPasses;
    }

    /**
     * @param TemplateNode                         $template
     * @param ComponentFactory\ComponentProperties $component
     */
    public function componentPass( TemplateNode $template, ComponentFactory\ComponentProperties $component ) : void
    {
        ( new NodeTraverser() )->traverse(
            $template,
            function( Node $node ) use ( $component ) : int|Node {
                // Skip expression nodes, as a component cannot exist there
                if ( $node instanceof ExpressionNode ) {
                    return NodeTraverser::DontTraverseChildren;
                }

                // Components are only called from ElementNodes
                if ( ! $node instanceof ElementNode ) {
                    return $node;
                }

                if ( ! $component->targetTag( $node->name ) ) {
                    return $node;
                }

                $build = clone $this->factory->getComponent( $component->name );

                if ( $component->static ) {
                    $build->create(
                        ComponentNode::nodeArguments( new NodeCompiler( $node ) ),
                        $component->tagged,
                    );

                    // TODO : Create a ComponentCompiler that does not include the FrameworkExtension
                    $html = $build->render( new TemplateCompiler() );

                    dump( $html );
                    return $html ? new StaticNode( $html, $node->position ) : $node;
                }

                dump( $build );

                // Get ComponentProperties, if one matches the Node->tag
                // if ( !$component = $this->factory->getComponentProperties( $node->name ) ) {
                //     return $node;
                // }

                // if ( $render !== $component->render ) {
                //     return $node;
                // }
                //
                // if ( 'static' === $component->render ) {
                //     $html = $this->factory->render(
                //         $component->class::componentName(),
                //         $component->class::nodeArguments( new NodeCompiler( $node ) ),
                //     );
                //     return new TextNode( $html );
                // }
                //
                // if ( 'runtime' === $component->render ) {
                //     return $this->factory->get( $component )->templateNode( new NodeCompiler( $node ) );
                // }

                return $node;
            },
        );
    }

    #[Override]
    public function getProviders() : array
    {
        return [
            'component' => $this->factory,
        ];
    }
}
