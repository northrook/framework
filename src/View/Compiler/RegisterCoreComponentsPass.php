<?php

declare(strict_types=1);

namespace Core\View\Compiler;

class RegisterCoreComponentsPass extends RegisterComponentPass
{
    public function register() : array
    {
        $coreComponent = \glob( \dirname( __DIR__, 2 ).'/UI/Component/Icon.php' );

        if ( ! $coreComponent ) {
            $this->console->warning( 'No Core Components found.' );
            return [];
        }

        return $coreComponent;
    }
}
