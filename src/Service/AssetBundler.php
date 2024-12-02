<?php

declare(strict_types=1);

namespace Core\Service;

use Core\Framework\Autowire\Pathfinder;
use Core\Service\AssetBundler\{AssetManifest, AssetMap, CompiledAsset};
use Northrook\{JavaScriptMinifier, Logger\Log, StylesheetMinifier};
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
                $bundleName .= '.css';
                $compiled = $this->compileStylesheet( $bundleName, $assetType['css'] );
            }
            elseif ( $assetType['js'] ?? false ) {
                $bundleName .= '.js';
                $compiled = $this->compileScript( $bundleName, $assetType['js'] );
            }
            else {
                Log::warning(
                    'Unable to compile unknown asset type: {assetType}.',
                    ['assetType' => \implode( ', ', \array_keys( $assetType ) )],
                );

                continue;
            }
            $bundled[$compiled->name] = $compiled->report;
            $this->assets->set(
                $compiled->name,
                [
                    'path'      => $compiled->path,
                    'bundle'    => $bundleName,
                    'timestamp' => \time(),
                ],
            );
        }

        return $bundled;
    }

    protected function compileStylesheet( string $name, array $paths ) : CompiledAsset
    {
        $savePath = $this->pathfinder( 'dir.assets.storage/build/'.Str::end( $name, '.css' ) );

        try {
            $compiler   = new StylesheetMinifier( $paths );
            $stylesheet = $compiler->minify();

            if ( $stylesheet ) {
                $report = $compiler->report();
                $this->filesystem()->dumpFile( $savePath, $stylesheet );
            }
            else {
                $report   = 'Resulting stylesheet is empty, and was not saved.';
                $savePath = '';
            }
        }
        catch ( Exception $exception ) {
            $report = $exception->getMessage();
        }

        return new CompiledAsset( $name, $savePath, $report );
    }

    protected function compileScript( string $name, array $paths ) : CompiledAsset
    {
        $savePath = $this->pathfinder( 'dir.assets.storage/build/'.Str::end( $name, '.js' ) );

        try {
            $compiler = new JavaScriptMinifier( $paths );
            $script   = $compiler->minify();

            if ( $script ) {
                $report = 'Minified script.';
                $this->filesystem()->dumpFile( $savePath, $script );
            }
            else {
                $report = 'Resulting stylesheet is empty, and was not saved.';
            }
        }
        catch ( Exception $exception ) {
            $report = $exception->getMessage();
        }

        return new CompiledAsset( $name, $savePath, $report );
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
