<?php

declare(strict_types=1);

namespace Core\Service;

use Core\Framework\Autowire\Pathfinder;
use Core\Service\AssetBundler\{AssetManifest, AssetMap};
use Northrook\{JavaScriptMinifier, StylesheetMinifier};
use Core\Symfony\DependencyInjection\{ServiceContainerInterface, ServiceLocator};
use Support\Str;
use Symfony\Component\Filesystem\Filesystem;
use Exception;

final class AssetBundler implements ServiceContainerInterface
{
    use ServiceLocator, Pathfinder;

    private readonly AssetMap $map;

    private readonly Filesystem $filesystem;

    /**
     * @param AssetManifest $assets
     * @param array         $assetMap
     * @param string        $buildDirectory
     */
    public function __construct(
        private readonly AssetManifest $assets,
        array                          $assetMap,
        private readonly string        $buildDirectory,
    ) {
        $this->map = new AssetMap( $assetMap, true );
    }

    /**
     * @param null|string     $bundle
     * @param null|'css'|'js' $type
     *
     * @return array<string, Exception|string>
     */
    public function compile( ?string $bundle = null, ?string $type = null ) : array
    {
        $bundled = [];

        foreach ( $this->map( $bundle ) as $bundleName => $assetType ) {
            if ( $assetType['css'] ?? false ) {
                try {
                    $report = $this->compileStylesheet( $bundleName, $assetType['css'] );
                }
                catch ( Exception $report ) {
                }
                $bundled[$bundleName] = $report;
            }
            elseif ( $assetType['js'] ?? false ) {
                $bundled[$bundleName] = $this->compileScript( $bundleName, $assetType['js'] );
            }
        }

        return $bundled;
    }

    protected function compileStylesheet( string $name, array $paths ) : string
    {
        $compiler = new StylesheetMinifier( $paths );

        $stylesheet = $compiler->minify();

        $savePath = $this->pathfinder( 'dir.assets.storage/build/'.Str::end( $name, '.css' ) );

        $this->filesystem()->dumpFile( $savePath, $stylesheet );

        return $compiler->report();
    }

    protected function compileScript( string $name, array $paths ) : Exception|string
    {
        $compiler = new JavaScriptMinifier( $paths );

        return __METHOD__;
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

    private function filesystem() : Filesystem
    {
        return $this->filesystem ??= new Filesystem();
    }
}
