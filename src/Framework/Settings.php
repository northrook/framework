<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\SettingsInterface;
use Exception\NotImplementedException;
use Northrook\ArrayStore;
use UnitEnum;
use InvalidArgumentException;

/**
 * @template TKey of array-key
 * @template TValue of mixed
 */
final readonly class Settings
{
    private ArrayStore $settings;

    /**
     * @param string $storageDirectory
     */
    public function __construct( string $storageDirectory )
    {
        $this->settings = new ArrayStore( $storageDirectory, $this::class );
    }

    public function get(
        string                                    $setting,
        mixed                                     $default = null,
        ?string                                   $set = null,
        UnitEnum|float|int|bool|array|string|null $value = null,
    ) : mixed {
        $get = $this->settings->get( $setting );

        if ( $get ) {
            return $set;
        }

        // TODO: Handle setting of new value if user has permissions.

        if ( $default ) {
            return $default;
        }

        throw new InvalidArgumentException();
    }

    public function versions( string $settings, ?int $limit = null ) : array
    {
        throw new NotImplementedException( $this::class, SettingsInterface::class );
    }

    public function restore( string $setting, int $versionId ) : bool
    {
        throw new NotImplementedException( $this::class, SettingsInterface::class );
    }

    public function add( array $parameters ) : void
    {
        $this->settings->add( $parameters );
    }

    public function has( string $setting ) : bool
    {
        return $this->settings->has( $setting );
    }

    public function all() : array
    {
        return $this->settings->all();
    }

    public function reset() : void
    {
        // $this->settings->clear();
        throw new NotImplementedException( $this::class, SettingsInterface::class );
    }

    public function remove( string $name ) : void
    {
        throw new NotImplementedException( $this::class, SettingsInterface::class );
        // $this->settings->delete( $name );
    }

    public function set( string $name, UnitEnum|float|int|bool|array|string|null $value ) : void
    {
        $this->settings->set( $name, $value );
    }
}
