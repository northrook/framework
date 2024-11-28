<?php

declare(strict_types=1);

namespace Core\View\Latte\Extension;

use Closure;
use Core\View\IconService;
use Latte\Runtime\{Html, HtmlStringable};

final class IconPackExtension extends \Latte\Extension
{
    /**
     * @param Closure(): IconService $iconPack
     */
    public function __construct(
        private readonly Closure $iconPack,
    ) {
    }

    public function getFunctions() : array
    {
        return [
            'icon'     => [$this, 'getIcon'],
            'iconPack' => [$this, 'getIconPack'],
        ];
    }

    public function getIcon( string $name, array $attributes = [] ) : HtmlStringable
    {
        $icon = $this->getIconService()->iconPack->get( $name, $attributes );
        return new Html( $icon );
    }

    private function getIconService() : IconService
    {
        return ( $this->iconPack )();
    }
}
