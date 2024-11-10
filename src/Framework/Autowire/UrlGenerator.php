<?php

declare( strict_types = 1 );

namespace Core\Framework\Autowire;

use Core\Framework\DependencyInjection\ServiceContainer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait UrlGenerator
{
    use ServiceContainer;

    final protected function urlGenerator() : UrlGeneratorInterface
    {
        return $this->serviceLocator( UrlGeneratorInterface::class );
    }

    public function generateRoutePath( string $name, array $parameters = [], bool $relative = false ) : string
    {
        return $this->urlGenerator()->generate(
                $name,
                $parameters,
                $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH,
        );
    }

    public function generateRouteUrl( string $name, array $parameters = [], bool $relative = false ) : string
    {
        return $this->urlGenerator()->generate(
                $name,
                $parameters,
                $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
