<?php

namespace Core\Service;

use Core\Assets\AssetFactory;
use Core\Assets\Factory\Asset\{ScriptAsset, StyleAsset};
use Core\Service\DesignSystem\StyleFramework;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class AssetManager extends \Core\Assets\AssetManager
{
    final public function __construct(
        AssetFactory     $factory,
        ?CacheInterface  $cache = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct( $factory, $cache, $logger );

        $this->factory->addAssetModelCallback(
            'script.core',
            function( ScriptAsset $asset ) : ScriptAsset {
                // $asset->prefersInline();

                return $asset;
            },
        );

        $this->factory->addAssetModelCallback(
            'style.core',
            function( StyleAsset $asset ) : StyleAsset {
                $style = new StyleFramework();

                $asset->addSource( $style->style() );

                $localAssets = $asset->pathfinder->getFileInfo(
                    path      : 'dir.core.assets/styles/core',
                    assertive : true,
                );

                foreach ( $localAssets->glob( '/*.css' ) as $localAsset ) {
                    $asset->addSource( $localAsset );
                }

                $asset->prefersInline();

                return $asset;
            },
        );
    }
}
