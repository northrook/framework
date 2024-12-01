<?php

declare(strict_types=1);

namespace Core\Service;

use Core\Framework\Autowire\Pathfinder;
use Core\Service\AssetBundler\AssetManifest;
use Core\Symfony\DependencyInjection\{ServiceContainerInterface, ServiceLocator};

final class AssetBundler implements ServiceContainerInterface
{
    use ServiceLocator, Pathfinder;

    /**
     * @param AssetManifest  $assets
     * @param array          $assetMap
     */
    public function __construct(
        private readonly AssetManifest $assets,
        private array                  $assetMap = [],
    ) {
        dump( $this );
    }
}
