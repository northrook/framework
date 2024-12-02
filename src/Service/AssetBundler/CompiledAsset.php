<?php

namespace Core\Service\AssetBundler;

/**
 * @internal
 */
final readonly class CompiledAsset
{
    public string $type;

    public function __construct(
        public string $name,
        public string $path,
        public string $report,
    ) {
        $this->type = \pathinfo( $this->path, PATHINFO_EXTENSION );
    }
}
