<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel as FrameworkKernel;
use Symfony\Component\HttpKernel\Kernel as HttpKernel;

final class Kernel extends HttpKernel
{
    use FrameworkKernel\MicroKernelTrait;

    public function hasContainer() : bool
    {
        return isset( $this->container );
    }
}
