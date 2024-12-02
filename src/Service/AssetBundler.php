<?php

declare(strict_types=1);

namespace Core\Service;

use Core\Framework\Autowire\Pathfinder;
use Core\Service\AssetBundler\{AssetManifest, AssetMap};
use Northrook\StylesheetMinifier;
use Core\Symfony\DependencyInjection\{ServiceContainerInterface, ServiceLocator};

final class AssetBundler implements ServiceContainerInterface
{
    use ServiceLocator, Pathfinder;

    private readonly AssetMap $map;

    /**
     * @param AssetManifest $assets
     * @param array         $assetMap
     * @param string        $buildDirectory
     */
    public function __construct(
        private readonly AssetManifest $assets,
        array                          $assetMap = [],
        private readonly string        $buildDirectory,
    ) {
        dump(
            $this::class.' build directories:',
            [
                'buildDirectory'     => $this->buildDirectory,
                'dir.assets.storage' => $this->pathfinder( 'dir.assets.storage' ),
                'dir.public.assets'  => $this->pathfinder( 'dir.public.assets' ),
            ],
        );
        $this->map = new AssetMap( $assetMap, true );
    }

    /**
     * Returns `true` if `$bundle` successfully compiled a bundle.
     * Returns array of [bundleName => status] when compiling all.
     * Returns `false` on failure.
     *
     * @param null|string     $bundle
     * @param null|'css'|'js' $type
     *
     * @return array<string, bool>|bool
     */
    public function compile( ?string $bundle = null, ?string $type = null ) : bool|array
    {
        $assetBundle = [];

        foreach ( $this->map( $bundle ) as $bundleName => $assetType ) {
            if ( $assetType['css'] ?? false ) {
                $assetBundle[$bundleName]['css'] = $this->compileStylesheet( $assetType['css'] );
            }
        }

        dump( $assetBundle );
        return false;
    }

    protected function compileStylesheet( array $paths ) : ?string
    {
        $compiler = new StylesheetMinifier( $paths );

        return $compiler->toString();
    }

    protected function assetTypeCompiler( $assetType )
    {
    }

    /**
     * @param ?string $get
     *
     * @return array<string, array<string,string>|string>
     */
    private function map( ?string $get = null ) : array
    {
        return $get ? [$get => $this->map->get( $get )] : $this->map->all();
    }
}
