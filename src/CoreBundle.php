<?php

declare(strict_types=1);

namespace Core;

use Core\Symfony\Compiler\{AutodiscoverServicesPass, AutowireActionsPass};
use Override;
use Core\View\Compiler\RegisterViewComponentsPass;
use Core\Framework\Compiler\{ApplicationConfigPass,
    RegisterCoreServicesPass,
    SettingsCompilerPass
};
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
        'path.asset_manifest' => '%dir.root%/var/assets/manifest.php',

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
        '../config/framework/memoization.php',
        '../config/framework/http.php',
        '../config/framework/assets.php',
        '../config/framework/response.php',
        '../config/framework/services.php',
        '../config/framework/settings.php',
        '../config/framework/pathfinder.php',
        '../config/framework/profiler.php',
        '../config/framework/toasts.php',
        '../config/framework/controllers/public.php',
        '../config/view/components.php',
        // '../config/view/latte.php',
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
            ->addCompilerPass( new AutodiscoverServicesPass() )
            ->addCompilerPass( new AutowireActionsPass() )
            ->addCompilerPass( new RegisterCoreServicesPass() )
            ->addCompilerPass( new ApplicationConfigPass() )
            ->addCompilerPass( new SettingsCompilerPass() )
            ->addCompilerPass( new RegisterViewComponentsPass() );
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
        \array_map( [$container, 'import'], $this::CONFIG );
    }
}
