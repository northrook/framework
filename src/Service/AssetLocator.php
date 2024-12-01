<?php

namespace Core\Service;

use Core\Service\AssetBundler\AssetManifest;

final readonly class AssetLocator
{
    public function __construct(
        public AssetManifest $assetMap,
    ) {
    }
}
