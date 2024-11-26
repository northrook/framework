<?php

namespace Core\UI\Component;

use Core\View\Attribute\ViewComponent;
use Core\View\Component;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Node\ComponentNode;
use Core\View\Template\TemplateCompiler;
use Northrook\Logger\Log;

#[ViewComponent( ['a', 'a:primary', 'a:underline'] )]
final class Anchor extends Component implements Component\NodeInterface
{
    use Component\InnerContent;

    protected const ?string TAG = 'a';

    /**
     * @param ?string $set
     *
     * @return $this
     */
    public function setHref( ?string $set = null ) : self
    {
        // $set ??= $this->attributes->pull( 'href' ) ?? '#';

        // if ( '#' === $set ) {
        //     // Log::notice(
        //     //     'The {tag} component has {attribute} set to {href}.',
        //     //     [
        //     //             'tag'       => $this->component->tag,
        //     //             'attribute' => 'href',
        //     //             'href'      => $set,
        //     //     ],
        //     // );
        // }

        // TODO : Validate schema://example.com
        // TODO : parse mailto:, tel:, sms:, etc
        // TODO : handle executable prefix javascript:url.tld
        // TODO : hreflang
        // TODO : sniff rel=referrerPolicy
        // TODO : sniff _target
        // TODO : sniff type
        // TODO : sniff name|id

        // $this->attributes->set( 'href', $set );
        return $this;
    }

    protected function primary() : void
    {
        // $this->attributes->class( 'primary' );
    }

    protected function underline() : void
    {
        // $this->attributes->class( 'underline' );
    }

    protected function compile( TemplateCompiler $compiler ) : string
    {
        // $this->setHref();
        return $compiler->render( __DIR__.'/anchor.latte', $this, cache : false );
    }

    public function node( NodeCompiler $node ) : ComponentNode
    {
        return new ComponentNode( $this->name, $node );
    }
}
