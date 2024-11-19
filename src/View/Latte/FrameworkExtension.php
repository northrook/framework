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
            'static_component_pass' => fn( TemplateNode $template ) => $this->componentPass(
                $template,
                'static',
            ),
            'runtime_component_pass' => fn( TemplateNode $template ) => $this->componentPass(
                $template,
                'runtime',
            ),
        ];
    }

    /**
     * @param TemplateNode              $template
     * @param 'live'|'runtime'|'static' $render
     */
    public function componentPass( TemplateNode $template, string $render ) : void
    {
        ( new NodeTraverser() )->traverse(
            $template,
            function( Node $node ) use ( $render ) : int|Node {
                // Skip expression nodes, as a component cannot exist there
                if ( $node instanceof ExpressionNode ) {
                    return NodeTraverser::DontTraverseChildren;
                }

                // Components are only called from ElementNodes
                if ( ! $node instanceof ElementNode ) {
                    return $node;
                }

                // Get ComponentProperties, if one matches the Node->tag
                if ( ! $component = $this->factory->getComponentProperties( $node->name ) ) {
                    return $node;
                }

                if ( $render !== $component->render ) {
                    return $node;
                }

                if ( 'static' === $component->render ) {
                    $html = $this->factory->render(
                        $component->class::componentName(),
                        $component->class::nodeArguments( new NodeCompiler( $node ) ),
                    );
                    return new AuxiliaryNode( static fn() => "echo '{$html}';" );
                }

                if ( 'runtime' === $component->render ) {
                    return $this->factory->build( $component )->templateNode( new NodeCompiler( $node ) );
                }

                dump( $component );

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
