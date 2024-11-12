<?php

declare(strict_types=1);

namespace Core\View\Compiler;

use Core\Framework\DependencyInjection\CompilerPass;
use Core\View\{ComponentFactory};
use Symfony\Component\DependencyInjection\{ContainerBuilder};
class RegisterViewComponentsPass extends CompilerPass
{
    public function compile( ContainerBuilder $container ) : void
    {
        $coreComponent    = \dirname( __DIR__, 2 ).'/UI/Component/*.php';
        $componentFactory = $container->getDefinition( ComponentFactory::class );

        $components = [];

        foreach ( \glob( $coreComponent ) as $filePah ) {
            $register = new ComponentModel( $filePah );

            $components[$register->name] = [
                'name'  => $register->name,
                'class' => $register->class,
                'tags'  => $register->tags,
            ];
        }

        $componentFactory->replaceArgument( 0, $components );
    }
}
