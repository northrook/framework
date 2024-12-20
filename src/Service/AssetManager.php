<?php

namespace Core\Service;

use Core\Assets\AssetFactory;
use Core\Assets\Factory\Asset\StyleAsset;
use Core\Service\DesignSystem\StyleFramework;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class AssetManager extends \Core\Assets\AssetManager
{
    final public function __construct(
            AssetFactory     $factory,
            ?CacheInterface  $cache = null,
            ?LoggerInterface $logger = null,
    )
    {
        parent::__construct( $factory, $cache, $logger );

        $this->factory->addAssetModelCallback(
                'style.core',
                function( StyleAsset $asset ) : StyleAsset
                {
                    $style = new StyleFramework();

                    $asset->addSource( $style->style() );

                    $localAssets = $asset->pathfinder->getFileInfo( 'dir.core.assets/styles/core' );

                    foreach ( $localAssets->glob( '/*.css' ) as $localAsset ) {
                        dump( $localAsset );
                        $asset->addSource( $localAsset );
                    }

                    return $asset;
                },
        );
    }
}
