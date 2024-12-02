<?php

namespace Core\Service;

use Core\Service\AssetBundler\AssetManifest;

final readonly class AssetLocator
{
    public function __construct(
        public AssetManifest $assetManifest,
    ) {
    }

    public function get( string $bundle ) : array
    {
        return $this->assetManifest->get( $bundle );
    }

    public function getScript( string $name ) : ?string
    {
        $asset = $this->assetManifest->get( $name );

        dump( $asset );

        return __METHOD__;
    }

    public function getStylesheet( string $name ) : ?string
    {
        $asset = $this->assetManifest->get( $name );

        dump( $asset );

        return __METHOD__;
    }
}
