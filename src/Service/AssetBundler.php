<?php

declare(strict_types=1);

namespace Core\Service;

use Core\Framework\Autowire\Pathfinder;
use Core\Service\AssetBundler\{AssetManifest, AssetMap};
use Core\Symfony\DependencyInjection\{ServiceContainerInterface, ServiceLocator};

final class AssetBundler implements ServiceContainerInterface
{
    use ServiceLocator, Pathfinder;

    private readonly AssetMap $map;

    /**
     * @param AssetManifest $assets
     * @param array         $assetMap
     */
    public function __construct(
        private readonly AssetManifest $assets,
        array                          $assetMap = [],
    ) {
        $this->map = new AssetMap( $assetMap );
        dump( $this );
    }

    /**
     * Returns `true` if `$bundle` successfully compiled a bundle.
     * Returns array of [bundleName => status] when compiling all.
     * Returns `false` on failure.
     *
     * @param null|string $bundle
     *
     * @return array<string, bool>|bool
     */
    public function compile( ?string $bundle = null ) : bool|array
    {
        dump( $this );
        return false;
    }
}
