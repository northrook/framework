<?php

declare(strict_types=1);

namespace Core\View\Attribute;

use Attribute;
use Northrook\HTML\Element\Tag;
use Northrook\Logger\Log;

// :: Replace Node in template, or as Runtime Callable via Factory
// :: Priority - nodes like the Icon needs to be parsed first, as they may be deeply nested

#[Attribute( Attribute::TARGET_CLASS )]
final readonly class ComponentNode
{
    public array $tags;

    /**
     * @param non-empty-lowercase-string[] $tag
     * @param bool                         $static
     */
    public function __construct( string|array $tag, public bool $static = false )
    {
        if ( \is_string( $tag ) ) {
            $tag = [$tag];
        }

        foreach ( $tag as $string ) {
            $htmlTag = \strstr( $string, ':', true );
            if ( ! \in_array( $htmlTag, Tag::TAGS, true ) ) {
                Log::warning( 'Unknown tag: '.$string );
            }
        }
        $this->tags = $tag;
    }
}
