<?php

declare(strict_types=1);

namespace Core;

use Core\Framework\Compiler\{ApplicationConfigPass, RegisterCoreServicesPass};
use Core\View\Compiler\RegisterCoreComponentsPass;
use Override;
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
        'dir.assets'         => '%dir.root%/assets',
        'dir.public.assets'  => '%dir.root%/public/assets',
        'dir.assets.storage' => '%dir.root%/var/assets',
        'dir.core.assets'    => '%dir.core%/assets',
        'dir.assets.themes'  => '%dir.core%/assets',
        //
        'path.asset_manifest' => '%dir.root%/var/assets/manifest.array.php',

        // Templates
        'dir.templates'      => '%dir.root%/templates',
        'dir.core.templates' => '%dir.core%/templates',

        // Cache
        'dir.cache'       => '%kernel.cache_dir%',
        'dir.cache.latte' => '%kernel.cache_dir%/latte',

        // Themes
        'path.theme.core' => '%dir.core%/config/themes/core.php',

        // Settings DataStore
        'path.settings_store' => '%dir.var%/settings.array.php',
    ];

    /** @var string[] */
    private const array CONFIG = [
        '../config/framework/assets.php',
        '../config/framework/response.php',
        '../config/framework/services.php',
        '../config/framework/settings.php',
        '../config/framework/profiler.php',
        '../config/framework/controllers/public.php',
        '../config/view/components.php',
        '../config/view/latte.php',
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
        parent::build( $container );

        $container
            ->addCompilerPass( new RegisterCoreServicesPass() )
            ->addCompilerPass( new ApplicationConfigPass() )
            ->addCompilerPass( new RegisterCoreComponentsPass() );
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
