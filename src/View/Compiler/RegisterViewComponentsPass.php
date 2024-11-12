<?php

declare(strict_types=1);

namespace Core\View\Compiler;

use Core\Framework\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterViewComponentsPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        dump( $this, $container );
    }
}
