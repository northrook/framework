<?php

namespace Core\UI\Component;

use Core\UI\Component;
use Core\View\Attribute\ComponentNode;

#[ComponentNode( 'a', 'a:primary', 'a:underline' )]
final class Anchor extends Component
{
    public readonly string $href;

    public function __construct(
        string       $href,
        string       $tag = 'a',
        array        $attributes = [],
        array|string $content = [],
        ?string      $uniqueId = null,
    ) {
        $this->setHref( $href, $attributes );
        parent::__construct( $tag, $attributes, $content, $uniqueId );
    }

    /**
     * @param ?string                 $set
     * @param array<array-key, mixed> $attributes
     *
     * @return $this
     */
    public function setHref( ?string $set, array &$attributes ) : self
    {
        if ( ! $set && \is_string( $attributes['href'] ?? null ) ) {
            $set = $attributes['href'];
            unset( $attributes['href'] );
        }
        else {
            $set = '#';
        }

        // TODO : Validate schema://example.com
        // TODO : parse mailto:, tel:, sms:, etc
        // TODO : handle executable prefix javascript:url.tld
        // TODO : hreflang
        // TODO : sniff rel=referrerPolicy
        // TODO : sniff _target
        // TODO : sniff type
        // TODO : sniff name|id

        $this->href = $set;

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

    protected function build() : string
    {
        $this->attributes->set( 'href', $this->href );

        return (string) $this->element;
    }
}
