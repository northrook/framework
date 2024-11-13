<?php

namespace Core\View\Render;

use Northrook\HTML\Element;

final class HtmlContent
{
    private array $content;

    /**
     * @param null|array<array-key, mixed>|string $content
     */
    private function __construct( null|string|array $content )
    {
        // parse incoming $content - must result in an array of lines => nested element[lines]
        $this->content = (array) $content;
    }

    /**
     * @param array<array-key, array<string, mixed>|string>|string $content
     *
     * @return array<array-key, string>
     */
    public static function contentArray( string|array $content ) : array
    {
        $content = new HtmlContent( $content );

        return (array) $content->parseContent();
    }

    /**
     * @param array<array-key, array<string, mixed>|string>|string $content
     *
     * @return string
     */
    public static function contentString( string|array $content ) : string
    {
        $content = HtmlContent::contentArray( $content );

        $string = '';

        foreach ( $content as $item ) {
            $lastChar = \mb_substr( $string, -1 );
            $nextChar = \mb_substr( $item, 0, 1 );

            // If there is no previous character, or if the previous character is a closing tag
            // and the following character is non-word and non-whitespace character
            if ( ! $lastChar || ( '>' === $lastChar && \preg_match( '#[^\w\s]#', $nextChar ) ) ) {
                $string .= $item;
            }
            else {
                $string .= " {$item}";
            }
        }

        return $string;
    }

    /**
     * @param null|array<array-key, mixed> $array
     * @param null|int|string              $key
     *
     * @return array<array-key, mixed>|string
     */
    protected function parseContent( ?array $array = null, null|string|int $key = null ) : string|array
    {
        $array ??= $this->content;
        $tag        = null;
        $attributes = [];
        // If $key is string, this iteration is an element
        if ( \is_string( $key ) ) {
            $tag        = \strrchr( $key, ':', true );
            $attributes = $array['attributes'];
            $array      = $array['content'];

            // if ( \str_ends_with( $tag, 'icon' ) && $get = $attributes['get'] ?? null ) {
            //     unset( $attributes['get'] );
            //     return (string) new Icon( $tag, $get, $attributes );
            // }
        }

        $content = [];

        foreach ( $array as $elementKey => $value ) {
            $elementKey = $this->nodeKey( $elementKey, \gettype( $value ) );

            if ( \is_array( $value ) ) {
                $content[$elementKey] = $this->parseContent( $value, $elementKey );
            }
            else {
                self::appendTextString( $value, $content );
            }
        }

        if ( $tag ) {
            $element = new Element( $tag, $attributes, $content );

            return $element->__toString();
        }

        return $content;
    }

    protected function nodeKey( string|int $node, string $valueType ) : string|int
    {
        if ( \is_int( $node ) ) {
            return $node;
        }

        $index = \strrpos( $node, ':' );

        // Treat parsed string variables as simple strings
        if ( false !== $index && 'string' === $valueType && \str_starts_with( $node, '$' ) ) {
            return (int) \substr( $node, $index++ );
        }

        return $node;
    }

    protected function appendTextString( string $value, array &$content ) : void
    {
        // Trim $value, and bail early if empty
        if ( ! $value = \trim( $value ) ) {
            return;
        }

        $lastIndex = \array_key_last( $content );
        $index     = \count( $content );

        if ( \is_int( $lastIndex ) ) {
            if ( $index > 0 ) {
                $index--;
            }
        }

        if ( isset( $content[$index] ) ) {
            $content[$index] .= " {$value}";
        }
        else {
            $content[$index] = $value;
        }
    }
}
