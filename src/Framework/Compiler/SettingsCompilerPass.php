<?php

declare(strict_types=1);

namespace Core\Framework\Compiler;

use Core\Symfony\DependencyInjection\CompilerPass;
use Northrook\ArrayStore;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Override;
use UnitEnum;

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
    #[Override]
    public function compile( ContainerBuilder $container ) : void
    {
        if ( ! $container->hasDefinition( 'core.settings_store' ) ) {
            $this->console->error( $this::class." cannot find required 'core.service_locator' definition." );
            return;
        }

        [$storagePath, $name] = $container->getDefinition( 'core.settings_store' )->getArguments();

        $settingsStore = new ArrayStore( $storagePath, $name);

        $settingsStore->setDefault( $this->getDefaultSettings() );
    }

    private function getDefaultSettings() : array
    {
        $kernelParamters = [
            'kernel.environment',
            'kernel.debug',
            'kernel.charset',
            'kernel.locale',
            'kernel.default_locale',
            'kernel.enabled_locales',
        ];

        $env         = [];
        $paths       = [];
        $directories = [];
        $settings    = [];

        foreach ( $this->parameterBag->all() as $key => $value ) {
            if ( \in_array( $key, $kernelParamters, true ) ) {
                [$key, $value] = $this->kernelParameter( $key, $value );
                $env[$key]     = $value;
            }

            if ( \str_starts_with( $key, 'dir.' ) ) {
                $paths[$key] = $value;
            }
            if ( \str_starts_with( $key, 'path.' ) ) {
                $paths[$key] = $value;
            }
            if ( \str_starts_with( $key, 'settings.' ) || \str_starts_with( $key, 'setting.' ) ) {
                [$key, $value] = $this->settingsParameter( $key, $value );
                $env[$key]     = $value;
            }
        }

        return [...$env, ...$paths, ...$directories, ...$settings];
    }

    private function kernelParameter( string $key, string|bool|array $value ) : array
    {
        $key = \trim( \strstr( $key, '.' ), " \n\r\t\v\0." );

        $key = match ( $key ) {
            'default_locale'  => 'locale',
            'enabled_locales' => 'locales',
            default           => $key,
        };

        return [$key, $value];
    }

    /**
     * @param string                                                   $key
     * @param null|array<string, mixed>|bool|float|int|string|UnitEnum $value
     *
     * @return array
     */
    private function settingsParameter(
        string                                    $key,
        array|null|bool|float|int|string|UnitEnum $value,
    ) : array {
        return [\trim( \strstr( $key, '.' ), " \n\r\t\v\0." ), $value];
    }
}
