<?php

namespace Core\Service;

use Core\Service\IconService\IconPack;
use Exception\NotImplementedException;
use Core\View\Render\{Icon};
use Core\View\Interface\{IconInterface, IconPackInterface, IconServiceInterface};

// :: Not part of [core-view]

final readonly class IconService implements IconServiceInterface
{
    public IconPack $iconPack;

    public function __construct()
    {
        $this->iconPack = new IconPack();
    }

    final public function getIcon(
        string  $name,
        array   $attributes = [],
        ?string $fallback = null,
    ) : ?IconInterface {
        $icon = $this->iconPack->get( $name, $attributes, $fallback );
        return $icon ? new Icon( $icon ) : null;
    }

    public function getIconPack( ?string $name = null ) : IconPackInterface
    {
        return $this->iconPack;
    }

    public function hasIcon( string $name, ?string $pack = null ) : bool
    {
        return $this->iconPack->has( $name );
    }

    public function hasIconPack( string $name ) : bool
    {
        throw new NotImplementedException( 'This method is not implemented yet.', IconServiceInterface::class );
    }
}
