<?php

declare(strict_types=1);

namespace Core\Assets;

use Core\Assets\Factory\Asset\StyleAsset;
use Core\Service\DesignSystem\StyleFramework;
use Core\Symfony\Interface\FilterInterface;

final class CoreStyleFilter implements FilterInterface
{
    /**
     * @param string $reference
     *
     * @return array{string, callable}
     */
    public static function callback( string $reference ) : array
    {
        return [
            $reference,
            [self::class, 'filter'],
        ];
    }

    public static function filter( StyleAsset $asset ) : StyleAsset
    {
        $style = new StyleFramework();

        $asset->addSource( $style->style(), true );

        $asset->prefersInline();

        return $asset;
    }
}
