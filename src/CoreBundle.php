<?php

declare(strict_types=1);

namespace Core;

use Core\Symfony\Compiler\{AutodiscoverServicesPass, AutowireActionsPass};
use Core\View\Compiler\{RegisterComponentAssetsPass, RegisterViewComponentsPass};
use Override;
use Core\Framework\Compiler\{ApplicationConfigPass,
    RegisterCoreServicesPass,
    SettingsCompilerPass
};
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class CoreBundle extends AbstractBundle
{
    /** @var array<string, array{0: non-empty-string, 1: int}|string> */
    public const array PARAMETERS = [
        'dir.root'   => '%kernel.project_dir%',
        'dir.var'    => '%dir.root%/var',
        'dir.public' => '%dir.root%/public',
        'dir.core'   => [__DIR__, 1],

        // Assets
        'dir.assets'        => '%dir.root%/assets',
        'dir.assets.public' => '%dir.root%/public/assets',
        'dir.assets.build'  => '%dir.root%/assets/build',
        'dir.core.assets'   => '%dir.core%/assets',
        'dir.assets.themes' => '%dir.core%/assets',
        'dir.assets.cache'  => __DIR__.'/var/assets',
        //
        //
        'path.asset_manifest' => '%dir.root%/var/asset.manifest',

        // Templates
        'dir.templates'      => '%dir.root%/templates',
        'dir.core.templates' => '%dir.core%/templates',

        // Cache
        'dir.cache'       => '%kernel.cache_dir%',
        'dir.cache.latte' => '%kernel.cache_dir%/latte',
        'dir.cache.view'  => '%kernel.cache_dir%/view',

        // Themes
        'path.theme.core' => '%dir.core%/config/themes/core.php',

        // Settings DataStore
        'path.settings_store'   => '%dir.root%/var/settings/data_store.php',
        'path.settings_history' => '%dir.root%/var/settings/history_store.php',
    ];

    /** @var string[] */
    private const array CONFIG = [
        '../config/framework/assets.php',
        '../config/framework/cache.php',
        '../config/framework/services.php',
        '../config/framework/pathfinder.php',
        '../config/framework/profiler.php',
        '../config/framework/toasts.php',
        '../config/framework/controllers/public.php',
        '../config/view/components.php',
        '../config/view/renderer.php',
    ];

    /**
     * @return string
     */
    #[Override]
    public function getPath() : string
    {
        return \dirname( __DIR__ );
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    #[Override]
    public function build( ContainerBuilder $container ) : void
    {
        $container
            ->addCompilerPass(
                pass     : new AutodiscoverServicesPass(),
                priority : 1_024,
            )
            ->addCompilerPass( new AutowireActionsPass() )
            ->addCompilerPass( new RegisterCoreServicesPass() )
            ->addCompilerPass( new ApplicationConfigPass() )
                // ->addCompilerPass( new SettingsCompilerPass() )
            ->addCompilerPass( new RegisterViewComponentsPass() )
            ->addCompilerPass(
                pass : new RegisterComponentAssetsPass(),
                type : PassConfig::TYPE_OPTIMIZE,
            );
    }

    /**
     * @param array<array-key, mixed> $config
     * @param ContainerConfigurator   $container
     * @param ContainerBuilder        $builder
     *
     * @return void
     */
    #[Override]
    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {
        foreach ( CoreBundle::PARAMETERS as $name => $value ) {
            if ( \is_array( $value ) ) {
                \assert(
                    // @phpstan-ignore-next-line | asserts are here to _assert_, we cannot assume type safety
                    \is_string( $value[0] ) && \is_int( $value[1] ),
                    CoreBundle::class.'::PARAMETERS only accepts strings, or an array of [__DIR__, LEVEL]',
                );
                $value = \dirname( $value[0], $value[1] );
            }
            $container->parameters()->set( $name, Pathfinder::normalize( $value ) );
        }

        \array_map( [$container, 'import'], $this::CONFIG );
    }
}
