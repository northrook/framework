<?php

namespace Core\Action;

use Core\Symfony\DependencyInjection\Autodiscover;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Autodiscover( tag : ['controller.service_arguments', 'core.service_locator'], autowire : true )]
final readonly class UrlGenerator
{
    public function __construct( private readonly UrlGeneratorInterface $urlGenerator ) {}

    public function generateRoutePath( string $name, array $parameters = [], bool $relative = false ) : string
    {
        return $this->urlGenerator->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH,
        );
    }

    public function generateRouteUrl( string $name, array $parameters = [], bool $relative = false ) : string
    {
        return $this->urlGenerator->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
