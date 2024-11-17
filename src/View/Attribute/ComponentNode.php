<?php

declare(strict_types=1);

namespace Core\View\Attribute;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;
use Northrook\HTML\Element\Tag;
use Northrook\Logger\Log;

// :: Replace Node in template, or as Runtime Callable via Factory
// :: Priority - nodes like the Icon needs to be parsed first, as they may be deeply nested

/**
 * @used-by \Core\View\Compiler\ComponentBuilder, \Core\View\ComponentFactory
 *
 * @author  Martin Nielsen
 */
#[Attribute( Attribute::TARGET_CLASS )]
final readonly class ComponentNode
{
    /** Rendered and updated from the front-end */
    public const string LIVE = 'live';

    /** Rendered directly into the {@see \Core\View\TemplateEngine} cache */
    public const string STATIC = 'static';

    /** Rendered by the {@see \Core\View\ComponentFactory} at runtime */
    public const string RUNTIME = 'runtime';

    public array $tags;

    /**
     * Configure how this {@see \Core\View\Component\ComponentInterface} is handled.
     *
     * ### Tag
     * Assign one or more HTML tags to trigger this component.
     *
     * Use the `:` separator to indicate a component subtype,
     * which will call a method of the same name.
     *
     * ### Type
     * Determines how the Component is rendered.
     * - `live`
     * - `static`
     * - `runtime` - default
     *
     * ### Allow children
     * Whether to allow innerHTML for this Component.
     *
     * @param non-empty-lowercase-string[] $tag
     * @param non-empty-lowercase-string   $type
     * @param bool                         $allowChildren
     */
    public function __construct(
        string|array  $tag = [],
        #[ExpectedValues( values : ['live', 'static', 'runtime'] )] public string $type = 'runtime',
        public bool   $allowChildren = true,
    ) {
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
