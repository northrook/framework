<?php

declare(strict_types=1);

namespace Core\Framework\Compiler;

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
final class AssetDiscoveryPass extends CompilerPass
{
    // protected readonly Pathfinder $pathfinder;
    //
    // protected readonly AssetManifest $manifest;
    //
    // protected readonly AssetLocator $locator;

    #[Override]
    public function compile( ContainerBuilder $container ) : void
    {
        return;

        // if ( ! $container->hasDefinition( AssetLocator::class ) ) {
        //     $this->console->error( 'Asset source locator not found' );
        //     return;
        // }
        //
        // $pathfinder = $container->getDefinition( Pathfinder::class );
        // $manifest   = $container->getDefinition( AssetManifest::class );
        // $locator    = $container->getDefinition( AssetLocator::class );
        //
        // // dump( $pathfinder->getConfigurator());
        //
        // $this->pathfinder = new ( $pathfinder->getClass() )(
        //     $pathfinder->getArgument( 0 ),
        //     $this->parameterBag,
        // );
        //
        // $this->manifest = new ( $manifest->getClass() )(
        //     $manifest->getArgument( 0 ),
        // );
        //
        // $this->locator = new ( $locator->getClass() )(
        //     $this->pathfinder,
        //     $this->manifest,
        // );
        //
        // $this->locator->discover();
    }
}
