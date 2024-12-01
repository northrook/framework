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
        /**
         * Initiate after {@see SettingsCompilerPass}, grabbing:
         * - `dir.assets`, `core.assets`, and any other `KEY.assets.*` that resolves to a valid diredctory.
         * - Scan each dir, ensure it has .css and/or .js somewhere.
         * - Scans nested directories.
         *
         * Create an {@see \Core\Service\AssetBundler\AssetMap} and pass it to the {@see \Core\Service\AssetBundler}.
         */
    }

    /**
     * - `.core/assets/styles/*.css` baseline styles
     * - `.core/assets/styles/admin/*.css`
     * - `.core/assets/styles/public/*.css`
     * - `.core/assets/styles/component/*.css`
     *
     * Why?
     *
     * @return void
     */
    private function discoverStylesheets() : void
    {
    }
}
