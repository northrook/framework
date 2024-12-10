<?php

declare(strict_types=1);

namespace Core\Framework\Autowire;

use Core\Symfony\DependencyInjection\ServiceContainerInterface;

/**
 * @phpstan-require-implements ServiceContainerInterface
 */
trait Pathfinder
{
    /**
     * @param ?string $get
     *
     * @return null|\Core\Pathfinder|string
     */
    final protected function pathfinder( ?string $get = null ) : \Core\Pathfinder|null|string
    {
        if ( $get ) {
            return $this->serviceLocator( \Core\Pathfinder::class )->get( $get );
        }
        return $this->serviceLocator( \Core\Pathfinder::class );
    }
}
