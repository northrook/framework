<?php

namespace Core\Framework\Controller;

use Core\Framework\Security;
use Core\Symfony\DependencyInjection\ServiceContainer;
use Core\Symfony\Interface\ServiceContainerInterface;

/**
 * @phpstan-require-implements ServiceContainerInterface
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
trait SecurityController
{
    use ServiceContainer;

    final protected function getSecurity() : Security
    {
        return $this->serviceLocator( Security::class );
    }
}
