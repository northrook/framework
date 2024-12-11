<?php

declare(strict_types=1);

namespace Core\Framework\Compiler;

use Core\Framework\Autowire\Toast;
use Core\Symfony\Console\Output;
use Override;
use Core\Symfony\DependencyInjection\CompilerPass;
use Support\{Normalize, Reflect};
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ReflectionClassConstant;
use Exception;

final class ApplicationConfigPass extends CompilerPass
{
    #[Override]
    public function compile( ContainerBuilder $container ) : void
    {
        $this->path( 'config/packages/debug.yaml' )->remove();

        $this->normalizePathParameters();

        $this
            ->generateToastMeta()
            ->generateAppKernel( true )
            ->generatePublicIndex(
                true,
                'Do not edit this file.',
                'It will be regenerated when the Container is built or updated.',
            )
            ->generateControllerRouteConfig()
            ->createConfigServices()
            ->configurePreload()
            ->coreControllerRoutes();
    }

    protected function normalizePathParameters() : void
    {
        $handle = [];

        foreach ( $this->parameterBag->all() as $key => $value ) {
            // Only parse prefixed keys
            if ( \str_starts_with( $key, 'dir.' ) || \str_starts_with( $key, 'path.' ) ) {
                // Skip pure-placeholders
                if ( \str_starts_with( $value, '%' ) && \str_ends_with( $value, '%' ) ) {
                    continue;
                }

                // Normalize and report
                try {
                    $value = Normalize::path( $value );
                    $this->parameterBag->set( $key, $value );
                    $handle[] = [Output::format( '[OK]', 'info' )."{$key} : {$value}"];
                }
                catch ( Exception $e ) {
                    $handle[] = [Output::format( '[OK]', 'info' )."{$key} : {$e->getMessage()}"];
                }
            }
        }

        if ( ! empty( $handle ) ) {
            Output::table( __METHOD__, $handle );
        }
    }

    protected function generateToastMeta() : self
    {
        $reflect = Reflect::class( Toast::class );

        $byConstants = $reflect->getConstants( ReflectionClassConstant::IS_PUBLIC );

        $status = \array_filter( $byConstants, static fn( $value ) => \is_string( $value ) );

        $toastStatusTypes = "'".\implode( "', '", $status )."'";

        $this->createPhpFile(
            '.phpstorm.meta.php/.toast_action.meta.php',
            <<<PHP
                <?php 
                    
                namespace PHPSTORM_META;
                    
                expectedArguments(
                    \Core\Framework\Autowire\Toast::__invoke(),
                    0,
                    {$toastStatusTypes}
                );
                PHP,
            true,
        );

        return $this;
    }

    protected function generateAppKernel( bool $override = false, string ...$comment ) : self
    {
        $this->createPhpFile(
            'src/Kernel.php',
            <<<PHP
                <?php
                       
                declare(strict_types=1);
                       
                namespace App;
                       
                use Symfony\Bundle\FrameworkBundle\Kernel as FrameworkKernel;
                use Symfony\Component\HttpKernel\Kernel as HttpKernel;
                       
                final class Kernel extends HttpKernel
                {
                    use FrameworkKernel\MicroKernelTrait;
                }
                PHP,
            $override,
            ...$comment,
        );

        return $this;
    }

    protected function generatePublicIndex( bool $override = false, string ...$comment ) : self
    {
        $this->createPhpFile(
            'public/index.php',
            <<<PHP
                <?php
                       
                declare(strict_types=1);
                       
                require_once dirname( __DIR__ ).'/vendor/autoload_runtime.php';
                       
                return static fn( array \$context ) => new \App\Kernel(
                    (string) \$context['APP_ENV'],
                    (bool) \$context['APP_DEBUG'],
                );
                PHP,
            $override,
            ...$comment,
        );

        return $this;
    }

    protected function createConfigServices( bool $override = false ) : self
    {
        $this->path( 'config/services.yaml' )->remove();

        $this->createPhpFile(
            'config/services.php',
            <<<PHP
                <?php
                    
                declare(strict_types=1);
                    
                use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
                    
                return static function( ContainerConfigurator \$container ) : void {
                    
                    \$services = \$container->services();
                    
                    // Defaults for App services.
                    \$services
                        ->defaults()
                        ->autowire()
                        ->autoconfigure();
                    
                    \$services
                        // Make classes in src/ available to be used as services.
                        ->load( "App\\\\", __DIR__ . '/../src/' )
                        // We do not want to autowire DI, ORM, or Kernel classes.
                        ->exclude(
                            [
                                __DIR__ . '/../src/DependencyInjection/',
                                __DIR__ . '/../src/Entity/',
                                __DIR__ . '/../src/Kernel.php',
                            ],
                        );
                };
                PHP,
            $override,
        );
        return $this;
    }

    protected function configurePreload( bool $override = false ) : self
    {
        $this->createPhpFile(
            'config/preload.php',
            <<<'PHP'
                <?php
                    
                declare(strict_types=1);
                    
                if (\file_exists(\dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')) {
                    \opcache_compile_file(\dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php');
                }
                PHP,
            $override,
        );

        return $this;
    }

    protected function generateControllerRouteConfig( bool $override = false ) : self
    {
        $this->path( 'config/routes.yaml' )->remove();
        $this->path( 'config/routes.php' )->remove();

        $routes = [
            'app.controller' => [
                'resource' => [
                    'path'      => '../../src/Controller/',
                    'namespace' => 'App\Controller',
                ],
                'type' => 'attribute',
            ],
        ];

        $this->createYamlFile( 'config/routes/app.yaml', $routes, true );

        // $this->createPhpFile(
        //         'config/routes.php',
        //         <<<PHP
        //             <?php
        //
        //             declare(strict_types=1);
        //
        //             use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
        //
        //             return static function( RoutingConfigurator \$routes ) : void {
        //                 \$routes->import(
        //                     [
        //                         'path'      => '../src/Controller/',
        //                         'namespace' => 'App\Controller',
        //                     ],
        //                     'attribute',
        //                 );
        //             };
        //             PHP,
        //         $override,
        // );

        return $this;
    }

    protected function coreControllerRoutes() : self
    {
        // TODO : Ensure we set the controller attribute namespaces correctly

        $routes = [
            'core.controller' => [
                'resource' => [
                    'path'      => '@CoreBundle/src/Controller',
                    'namespace' => 'Core\Controller',
                ],
                'type' => 'attribute',
            ],
        ];

        $this->createYamlFile( 'config/routes/core.yaml', $routes, true );

        return $this;
    }
}
