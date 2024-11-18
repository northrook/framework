<?php

declare(strict_types=1);

namespace Core\View\ComponentFactory;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class ComponentProperties
{
    /**
     * @param non-empty-lowercase-string                            $name
     * @param class-string<\Core\View\Component\ComponentInterface> $class
     * @param array<int, string>                                    $tags
     */
    public function __construct(
        public string $name,
        public string $class,
        public array  $tags,
    ) {
    }

    public static function from( array $properties ) : ComponentProperties
    {
        return new ComponentProperties( 'component', __CLASS__, ['a', 'b', 'c'] );
    }
}
