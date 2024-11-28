<?php

declare(strict_types=1);

namespace Core\View\Compiler;

class RegisterCoreComponentsPass extends RegisterComponentPass
{
    public function register() : array
    {
        // TODO : Register assets as well
        //        Should allow for stand-alone (like the heavy math for input:password),
        //        or as integrated into core js|css

        $coreComponent = \glob( \dirname( __DIR__, 3 ).'/components/*.php' );

        if ( ! $coreComponent ) {
            $this->console->warning( 'No Core Components found.' );
            return [];
        }

        return $coreComponent;
    }
}
