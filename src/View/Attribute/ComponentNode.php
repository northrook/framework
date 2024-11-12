<?php

declare(strict_types=1);

namespace Core\View\Attribute;

use Attribute;
use Northrook\HTML\Element\Tag;
use Northrook\Logger\Log;

#[Attribute( Attribute::TARGET_CLASS )]
final class ComponentNode
{
    public array $tags;

    /**
     * @param non-empty-lowercase-string ...$tag
     */
    public function __construct( string ...$tag )
    {
        foreach ( $tag as $string ) {
            $htmlTag = \strstr( $string, ':', true );
            if ( ! \in_array( $htmlTag, Tag::TAGS, true ) ) {
                Log::warning( 'Unknown tag: '.$string );
            }
        }
        $this->tags = $tag;
    }
}
