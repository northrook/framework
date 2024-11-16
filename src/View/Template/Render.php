<?php

namespace Core\View\Template;

use Core\View\Template\Compiler\NodeExporter;
use Latte\Compiler\Nodes\AuxiliaryNode;
use const Cache\AUTO;

/**
 * @internal
 */
final class Render
{

    public static function auxiliaryNode(
        string $component,
        array  $arguments = [],
        ?int   $cache = AUTO,
    ) : AuxiliaryNode {
        $export = new NodeExporter();

        return new AuxiliaryNode(
            static fn() : string => <<<EOD
                echo \$this->global->component->render(
                    component : {$export->string( $component )},
                    arguments : {$export->arguments( $arguments )},
                    cache     : {$export->cacheConstant( $cache )},
                );
                EOD,
        );
    }
}
