<?php

declare(strict_types=1);

namespace Core\Service;

use Core\Framework\Autowire\Pathfinder;
use Core\Symfony\DependencyInjection\{ServiceContainerInterface, ServiceLocator};
use Northrook\ArrayStore;

final class AssetBundler implements ServiceContainerInterface
{
    use ServiceLocator, Pathfinder;

    private ArrayStore $assets;

    /**
     * @param array $assetMap
     */
    public function __construct( array $assetMap )
    {
        $this->assets = new ArrayStore(
            $this->pathfinder( 'dir.cache/asset.manifest.php' ),
            $this::class,
        );

        dump( $assetMap, $this );
    }
}
