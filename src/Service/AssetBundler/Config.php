<?php

declare(strict_types=1);

namespace Core\Service\AssetBundler;

use InvalidArgumentException;

final class Config
{
    // public static function bundle(
    //         string $name,
    // ) : array
    // {
    //
    // }

    public static function stylesheet( string $name ) : array
    {
        return [Config::name( $name ), Config::key( "{$name}.css" )];
    }

    public static function script( string $name ) : array
    {
        return [Config::name( $name ), Config::key( "{$name}.js" )];
    }

    private static function name( string $name ) : string
    {
        self::validate( $name );
        return 'asset_bundle.'.\trim( $name, '.' );
    }

    private static function key( string $name ) : string
    {
        self::validate( $name );
        return \trim( $name, '.' );
    }

    /**
     * Enforce characters
     *
     * @param string $string
     *
     * @return void
     */
    private static function validate( string $string ) : void
    {
        if ( ! \preg_match( "/^[a-zA-Z0-9_\-.]+$/", $string ) ) {
            throw new InvalidArgumentException( <<<'MSG'
                The provided string contains illegal characters. 
                Only ASCII letters, numbers, hyphens, and underscores.
                MSG, );
        }
    }
}
