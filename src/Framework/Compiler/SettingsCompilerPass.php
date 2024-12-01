<?php

declare(strict_types=1);

namespace Core\Framework\Compiler;

use Core\Symfony\DependencyInjection\CompilerPass;
use Northrook\ArrayStore;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Override;

/**
 * Parse the {@see ParameterBagInterface}.
 * - `dir.*`
 * - `env` and `debug`
 * - `locale`.
 *
 * TODO : Create docs for reserved keys, and ensure Bundles and the Application can create default overrides.
 *
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class SettingsCompilerPass extends CompilerPass
{
    private readonly ArrayStore $settingsStore;

    #[Override]
    public function compile( ContainerBuilder $container ) : void
    {
        if ( ! $container->hasDefinition( 'core.settings_store' ) ) {
            $this->console->error( $this::class." cannot find required 'core.service_locator' definition." );
            return;
        }

        dump( $container->getDefinition( 'core.settings_store' ) );
    }
}
