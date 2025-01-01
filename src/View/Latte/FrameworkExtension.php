<?php

declare(strict_types=1);

namespace Core\View\Latte;

use Core\Framework\Autowire\UrlGenerator;
use Core\Symfony\DependencyInjection\ServiceContainerInterface;
use Core\View\ComponentFactory;
use Core\View\Latte\Node\InlineStringableNode;
use JetBrains\PhpStorm\Deprecated;
use Latte\Compiler\{Node, NodeTraverser};
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\TemplateNode;
use Latte\Extension as LatteExtension;
use Override;

#[Deprecated]
final class FrameworkExtension extends LatteExtension implements ServiceContainerInterface
{
    use UrlGenerator;

    private array $staticComponents;

    private array $nodeComponents;

    public function __construct( public readonly ComponentFactory $factory )
    {
        dump( $this::class );
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

        $componentPasses = [];

        foreach ( $this->staticComponents as $component ) {
            $componentPasses["static-{$component->name}-pass"] = fn( TemplateNode $template ) => $this->componentPass(
                $template,
                $component,
            );
        }

        foreach ( $this->nodeComponents as $component ) {
            $componentPasses["node-{$component->name}-pass"] = fn( TemplateNode $template ) => $this->componentPass(
                $template,
                $component,
            );
        }

        // dump( $componentPasses );

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
            // null,
            function( Node $node ) use ( $component ) : int|Node {
                // Skip expression nodes, as a component cannot exist there
                if ( $node instanceof ExpressionNode ) {
                    return NodeTraverser::DontTraverseChildren;
                }

                // Components are only called from ElementNodes
                if ( ! $node instanceof ElementNode ) {
                    return $node;
                }

                return $node;
                // if ( ! $component->targetTag( $node->name ) ) {
                // }

                // $parser = new NodeParser( $node );

                // if ( $component->static ) {
                //     $build = clone $this->factory->getComponent( $component->name );
                //     $build->create(
                //         ComponentNode::nodeArguments( $parser ),
                //         $component->tagged,
                //     );
                //
                //     // TODO : Create a ComponentCompiler that does not include the FrameworkExtension
                //     // $html = $build->render( new TemplateCompiler() );
                //     $html = $build->render( $this->serviceLocator( TemplateCompiler::class ) );
                //
                //     return $html ? new StaticNode( $html, $node->position ) : $node;
                // }
                //
                // return new ComponentNode( $component->name, $parser );
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
