<?php

declare(strict_types=1);

namespace Core\Framework\Autowire;

use Core\Framework\Controller;
use Core\Symfony\DependencyInjection\ServiceContainer;
use JetBrains\PhpStorm\Deprecated;
use Support\Interface\ActionInterface;
use Symfony\Component\HttpFoundation\Request;
use function Support\get_class_name;

#[Deprecated( 'Moving to Actions', ActionInterface::class )]
trait CurrentRequest
{
    use ServiceContainer;

    final protected function getRequest() : Request
    {
        return $this->serviceLocator( Request::class );
    }

    final protected function isHtmxRequest() : bool
    {
        return $this->getRequest()->attributes->get( 'htmx', false );
    }

    final protected function isManagedRequest() : bool
    {
        return \is_subclass_of(
            get_class_name( $this->getRequest()->attributes->get( '_controller' ) ),
            Controller::class,
        );
    }
}
