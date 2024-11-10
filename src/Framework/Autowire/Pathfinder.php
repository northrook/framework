<?php

declare(strict_types=1);

namespace Core\Framework\Autowire;

use Core\Framework\DependencyInjection\ServiceContainer;

trait Pathfinder
{
    use ServiceContainer;

    /**
     * @param  ?string                                $get
     * @return null|\Core\Framework\Pathfinder|string
     */
    final protected function pathfinder( ?string $get = null ) : \Core\Framework\Pathfinder|null|string
    {
        if ( $get ) {
            return $this->serviceLocator( \Core\Framework\Pathfinder::class )->get( $get );
        }
        return $this->serviceLocator( \Core\Framework\Pathfinder::class );
    }
}
