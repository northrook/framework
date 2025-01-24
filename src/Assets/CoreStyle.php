<?php

declare(strict_types=1);

namespace Core\Assets;

use Core\Assets\Factory\Asset\StyleAsset;
use Core\Assets\Factory\Compiler\AssetArgument;
use Core\Service\DesignSystem\StyleFramework;

/**
 * @internal
 */
final class CoreStyle extends AssetArgument
{
    public static function filter( StyleAsset $model ) : StyleAsset
    {
        $style = new StyleFramework();

        $model->addSource( $style->style(), true );

        $model->prefersInline();

        return $model;
    }
}
