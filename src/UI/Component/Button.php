<?php

namespace Core\UI\Component;

use Core\View\Component\ComponentBuilder;
use Core\View\Component\ComponentInterface;
use Core\View\Template\Compiler\NodeCompiler;
use Latte\Compiler\Nodes\AuxiliaryNode;

final class Button extends ComponentBuilder {

    protected function build() : string
    {
        return 'button!';
    }

    public static function templateNode( NodeCompiler $node ) : AuxiliaryNode
    {
        // TODO: Implement templateNode() method.
    }
}
