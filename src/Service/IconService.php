<?php

namespace Core\Service;

use Core\Service\IconService\IconPack;
use Core\View\Template\View;
use BadMethodCallException;

// use Core\View\Render\{Icon};
// use Core\View\Interface\{IconInterface, IconPackInterface, IconServiceInterface};

// :: Not part of [core-view]

// Should be lazy
final readonly class IconService
{
    public IconPack $iconPack;

    public function __construct()
    {
        $this->iconPack = new IconPack();
    }

    /**
     * @param string       $name
     * @param array        $attributes
     * @param null|string  $fallback
     *
     * @return null|string
     */
    final public function getIcon(
        string  $name,
        array   $attributes = [],
        ?string $fallback = null,
    ) : ?string {
        return $this->iconPack->get( $name, $attributes, $fallback );
    }

    public function getIconPack( ?string $name = null ) : IconPack
    {
        return $this->iconPack;
    }

    public function hasIcon( string $name, ?string $pack = null ) : bool
    {
        return $this->iconPack->has( $name );
    }

    public function hasIconPack( string $name ) : bool
    {
        throw new BadMethodCallException( __METHOD__.' is not implemented.' );
    }
}
