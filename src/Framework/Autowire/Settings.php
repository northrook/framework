<?php

declare(strict_types=1);

namespace Core\Framework\Autowire;

use Core\Framework;
use Core\Symfony\DependencyInjection\ServiceContainer;

trait Settings
{
    use ServiceContainer;

    /**
     * @final
     *
     * @return Framework\Settings
     */
    final protected function settings() : Framework\Settings
    {
        return $this->serviceLocator( Framework\Settings::class );
    }
}
