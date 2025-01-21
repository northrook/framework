<?php

namespace Core\Framework\Compiler;

use Core\Symfony\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApplicationConfigPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        foreach ( $this->parameterBag->all() as $key => $value ) {
            if ( \str_starts_with( $key, 'config.' ) ) {
                dump( [$key => $container->getParameter( $key )] );
            }
        }
    }
}
