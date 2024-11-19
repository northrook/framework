<?php

namespace Core\UI\Component;

use Core\View\Attribute\ComponentNode;
use Core\View\Component\{ComponentBuilder};
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Northrook\Logger\Log;

#[ComponentNode( ['a', 'a:primary', 'a:underline'] )]
final class Anchor extends ComponentBuilder
{
    protected const ?string TAG = 'a';

    /**
     * @param ?string $set
     *
     * @return $this
     */
    public function setHref( ?string $set = null ) : self
    {
        $set ??= $this->element->attributes->pull( 'href' ) ?? '#';

        if ( '#' === $set ) {
            Log::notice(
                'The {tag} component has {attribute} set to {href}.',
                [
                    'tag'       => $this->element->tag,
                    'attribute' => 'href',
                    'href'      => $set,
                ],
            );
        }

        // TODO : Validate schema://example.com
        // TODO : parse mailto:, tel:, sms:, etc
        // TODO : handle executable prefix javascript:url.tld
        // TODO : hreflang
        // TODO : sniff rel=referrerPolicy
        // TODO : sniff _target
        // TODO : sniff type
        // TODO : sniff name|id

        $this->attributes->set( 'href', $set );
        return $this;
    }

    protected function primary() : void
    {
        $this->element->class( 'primary' );
    }

    protected function underline() : void
    {
        $this->element->class( 'underline' );
    }

    protected function compile() : string
    {
        $this->setHref();
        return (string) $this->element;
    }

    public function templateNode( NodeCompiler $node ) : AuxiliaryNode
    {
        return Render::templateNode(
            self::componentName(),
            $this::nodeArguments( $node ),
        );
    }

    public static function nodeArguments( NodeCompiler $node ) : array
    {
        return [
            'tag'        => $node->tag,
            'attributes' => $node->attributes(),
            'content'    => $node->parseContent(),
        ];
    }
}
