<?php

declare(strict_types=1);

namespace Core\Framework\Autowire;

use Core\Symfony\DependencyInjection\ServiceContainer;
use JetBrains\PhpStorm\Deprecated;
use Support\Interface\ActionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Deprecated( 'Moving to Actions', ActionInterface::class )]
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
