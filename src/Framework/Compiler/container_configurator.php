<?php

declare(strict_types=1);

namespace Core\Symfony;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

function parameter(
    ContainerConfigurator $configurator,
    string                $key,
    mixed                 $value,
    bool                  $throwOnDuplicate = false,
) : void {

    // Cannot merge here - make it a final protected method of CompilerPass?

}
