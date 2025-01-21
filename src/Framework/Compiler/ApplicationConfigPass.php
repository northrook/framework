<?php

namespace Core\Framework\Compiler;

use Core\Symfony\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApplicationConfigPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        dump( $this->parameterBag->all() );
    }
}
