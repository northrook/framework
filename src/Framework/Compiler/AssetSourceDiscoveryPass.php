<?php

declare(strict_types=1);

namespace Core\Framework\Compiler;

use Core\Pathfinder;
use Core\Service\AssetManager\{AssetLocator, AssetManifest};
use Core\Symfony\DependencyInjection\CompilerPass;
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
final class AssetSourceDiscoveryPass extends CompilerPass
{
    protected readonly AssetLocator $assetLocator;

    #[Override]
    public function compile( ContainerBuilder $container ) : void
    {
        if ( ! $container->hasDefinition( AssetLocator::class ) ) {
            $this->console->error( 'Asset source locator not found' );
            return;
        }

        $pathfinder   = $container->getDefinition( Pathfinder::class );
        $manifest     = $container->getDefinition( AssetManifest::class );
        $assetLocator = $container->getDefinition( AssetLocator::class );

        dump(
            $pathfinder,
            $manifest,
            $assetLocator,
        );
    }
}
