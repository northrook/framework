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
    /** @var string[] */
    private const array CONFIG = [
        '../config/framework/response.php',
        '../config/framework/services.php',
        '../config/framework/settings.php',
        '../config/framework/telemetry.php',
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
