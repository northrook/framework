<?php

declare(strict_types=1);

namespace Core\Assets;

use Core\Assets\Factory\Asset\StyleAsset;
use Core\Assets\Factory\Compiler\AssetArgument;
use Core\Service\DesignSystem\StyleFramework;

final class CoreStyle extends AssetArgument
{
    public static function filter( StyleAsset $asset ) : StyleAsset
    {
        $style = new StyleFramework();

        $asset->addSource( $style->style(), true );

        $asset->prefersInline();

        return $asset;
    }
}
