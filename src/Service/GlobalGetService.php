<?php

namespace Core\Service;

use Latte\Runtime\{Html, HtmlStringable};
use Support\PropertyAccessor;

/**
 * @property-read string $phpVersion
 */
final class GlobalGetService
{
    use PropertyAccessor;

    public function __get( string $property ) : HtmlStringable
    {
        $value = match ( $property ) {
            'phpVersion' => \phpversion(),
            default      => '',
        };

        return new Html( $value );
    }
}
