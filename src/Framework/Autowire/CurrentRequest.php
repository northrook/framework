<?php

declare(strict_types=1);

namespace Core\Framework\Autowire;

use Core\Framework\Controller;
use Core\Framework\DependencyInjection\ServiceContainer;
use Symfony\Component\HttpFoundation\{Request};
use function Support\get_class_name;

trait CurrentRequest
{
    use ServiceContainer;

    final protected function getRequest() : Request
    {
        return $this->serviceLocator( Request::class );
    }

    final protected function isManagedRequest() : bool
    {
        return \is_subclass_of( get_class_name( $this->getRequest()->attributes->get( '_controller' ) ), Controller::class );
    }
}
