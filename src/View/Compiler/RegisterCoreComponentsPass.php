<?php

declare(strict_types=1);

namespace Core\View\Compiler;

use Core\View\{ComponentFactory};
use Symfony\Component\DependencyInjection\{ContainerBuilder};

class RegisterCoreComponentsPass extends RegisterComponentPass
{
    public function register() : array
    {
        $coreComponent = \glob( \dirname( __DIR__, 2 ).'/UI/Component/*.php' );

        if ( ! $coreComponent ) {
            $this->console->warning( 'No Core Components found.' );
            return [];
        }

        $this->console->info( 'Registered '.\count( $coreComponent ).'  Core Components.' );

        return $coreComponent;
    }

    // public function compile( ContainerBuilder $container ) : void
    // {
    //     $coreComponent    = \dirname( __DIR__, 2 ).'/UI/Component/*.php';
    //     $componentFactory = $container->getDefinition( ComponentFactory::class );
    //
    //     $components = [];
    //     $tags       = [];
    //
    //     foreach ( \glob( $coreComponent ) as $filePah ) {
    //         $register = new ComponentModel( $filePah );
    //
    //         $components[$register->name] = [
    //             'name'  => $register->name,
    //             'class' => $register->class,
    //             'tags'  => $register->tags,
    //         ];
    //     }
    //
    //     $componentFactory->replaceArgument( 0, $components );
    // }
}
