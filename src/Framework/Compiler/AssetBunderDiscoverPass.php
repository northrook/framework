<?php

declare(strict_types=1);

namespace Core\Framework\Compiler;

use Core\Service\AssetBundler;
use Core\Service\AssetBundler\AssetManifest;
use Core\Symfony\DependencyInjection\CompilerPass;
use Core\View\ComponentFactory;
use Support\{FileInfo};
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Override;

/**
 * Find and register all project assets.
 *
 * The Discover pass parses:
 * - Project `./assets/` directory
 * - {@see \Core\CoreBundle} `./assets/` directory
 * - Any {@see \Core\View\Component} registered with the `core.component_locator`
 * -
 *
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class AssetBunderDiscoverPass extends CompilerPass
{
    private array $assets = [];

    #[Override]
    public function compile( ContainerBuilder $container ) : void
    {
        $hasManifest = $container->hasDefinition( AssetManifest::class );
        $hasBundler  = $container->hasDefinition( AssetBundler::class );

        if ( ! $hasManifest ) {
            $this->console->error( 'Required service '.AssetManifest::class.' is not defined.' );
        }
        if ( ! $hasBundler ) {
            $this->console->error( 'Required service '.AssetBundler::class.' is not defined.' );
        }

        if ( ! $hasBundler || ! $hasManifest ) {
            dump( __METHOD__.' no can do.' );
            return;
        }

        $assetBundlerService = $container->getDefinition( AssetBundler::class );

        $this->discoverAssetDirectories();

        if ( $container->hasDefinition( ComponentFactory::class ) ) {
            $componentFactory     = $container->getDefinition( ComponentFactory::class );
            $registeredComponents = $componentFactory->getArguments()[0] ?? [];
            $this->discoverComponentDirectories( $registeredComponents );
        }

        $assetBundlerService->replaceArgument( 1, $this->assets );
    }

    private function discoverAssetDirectories() : void
    {
        $skip = ['dir.public.assets', 'dir.assets.storage', 'dir.assets.themes'];

        $directories = [
            'core' => null,
            'app'  => null,
        ];

        foreach ( $this->parameterBag->all() as $key => $directory ) {
            if ( \in_array( $key, $skip ) ) {
                continue;
            }

            if ( \str_starts_with( $key, 'dir.' ) && \str_ends_with( $key, '.assets' ) ) {
                $offset = \strlen( 'dir.' );
                $inset  = \strlen( '.assets' );
                $key    = \substr( $key, $offset, -$inset ) ?: 'app';
                // dump( $key );
                $directories[$key] = $directory;
            }
        }

        $assets = [];

        foreach ( $directories as $key => $directory ) {
            if ( ! $directory ) {
                $this->console->error( 'Required directory '.$directory.' does not exist.' );

                continue;
            }

            foreach ( \glob( $directory.'/**/*.{css,js}', GLOB_BRACE ) as $file ) {
                $this->parseAsset( $file, $key );
            }
        }
    }

    private function discoverComponentDirectories( array $registeredComponents ) : void
    {
        foreach ( $registeredComponents as $component ) {
            $component = new ComponentFactory\ComponentProperties( ...$component );

            foreach ( $component->assets as $asset ) {
                $this->parseAsset( $asset, 'component' );
            }
        }
    }

    private function parseAsset( string $asset, ?string $key = null ) : void
    {
        $asset = new FileInfo( $asset );

        if ( ! $asset->isReadable() || ! $asset->isFile() ) {
            $this->console->error( "Asset '{$asset}' is ".( $asset->isFile() ? 'not readable.' : 'not a file.' ) );
        }

        $key = \implode(
            '.',
            \array_filter(
                [
                    $key,
                    $asset->getExtension(),
                    $asset->getBasename( '.'.$asset->getExtension() ),
                ],
            ),
        );

        if ( \in_array( $asset->getExtension(), ['css', 'js'] ) ) {
            $this->assets[$key] = $asset->getRealPath();
        }
        else {
            $this->console->warning( 'Unknown asset type: '.$asset->getExtension() );
        }
    }
}
