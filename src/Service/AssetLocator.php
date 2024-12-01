<?php

namespace Core\Service;

use Core\Service\AssetBundler\AssetMap;

final readonly class AssetLocator
{
    public function __construct(
        public AssetMap $assetMap,
    ) {
    }
}
