<?php

namespace Core\View\Component;

use Core\View\Render\HtmlContent;
use Latte\Runtime\Html;
use Northrook\HTML\AbstractElement;
use Northrook\HTML\Element\{Attribute, AttributeMethods, Attributes};
use Stringable;

/**
 * @internal
 *
 * @property-read Attribute $class
 * @property-read Attribute $style
 * @property-read string    $tag
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Component extends AbstractElement
{
    use AttributeMethods;

    /**
     * @param string                                          $tag        =  [ 'div', 'body', 'html', 'li', 'dropdown', 'menu', 'modal', 'field', 'fieldset', 'legend', 'label', 'option', 'select', 'input', 'textarea', 'form', 'tooltip', 'section', 'main', 'header', 'footer', 'div', 'span', 'p', 'ul', 'a', 'img', 'button', 'i', 'strong', 'em', 'sup', 'sub', 'br', 'hr', 'h', 'h1', 'h2', 'h3', 'h4', 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' ][$any]
     * @param array<string, mixed>                            $attributes
     * @param null|array<array-key, string|Stringable>|string $content
     */
    public function __construct(
        string $tag = 'div',
        array  $attributes = [],
        mixed  $content = null,
    ) {
        $this
            ->tag( $tag )
            ->assignAttributes( $attributes )
            ->content( HtmlContent::contentArray( $content??[] ) );
    }

    /**
     * @param string $property
     *
     * @return null|Attribute|string
     */
    public function __get( string $property ) : Attribute|string|null
    {
        // __get is mainly used to facilitate editing attributes

        return match ( $property ) {
            'tag' => (string) $this->tag,
            'class', 'style' => $this->attributes->edit( $property ),
            default => null,
        };
    }
}
