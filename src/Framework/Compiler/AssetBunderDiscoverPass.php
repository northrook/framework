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
final class AssetBunderDiscoverPass extends CompilerPass
{
    #[Override]
    public function compile( ContainerBuilder $container ) : void
    {
    }
}
