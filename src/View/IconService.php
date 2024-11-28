<?php

namespace Core\View;

use Core\View\Render\IconPack;

// :: Not part of [core-view]

final readonly class IconService
{
    public IconPack $iconPack;

    public function __construct()
    {
        $this->iconPack = new IconPack();
    }

    final protected function getIcon(
        string  $icon,
        array   $attributes = [],
        ?string $fallback = null,
    ) : ?string {
        return $this->iconPack->get( $icon, $attributes, $fallback );
    }
}
