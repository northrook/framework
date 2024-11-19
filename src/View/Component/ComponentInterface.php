<?php

namespace Core\View\Component;

use Core\View\Template\Compiler\NodeCompiler;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Stringable;

// The __constructor sort has to be a set standard
// We could have an abstract static for 'default' initialization?

interface ComponentInterface extends Stringable
{
    /*
    The Render::auxNode creates a callback, taking:
    class-string className - Factory : what Core\UI\Component to render - here we call ::build(..)
    array        arguments - the arguments to pass into ::build()
    array        autowire  - Factory : potential autowired services
    null|int     cache     - Factory : decides cache behaviour
     */

    /**
     * @param array<string, mixed> $arguments
     * @param null|string          $uniqueId
     *
     * @return ComponentInterface
     */
    public function build( array $arguments, ?string $uniqueId = null ) : ComponentInterface;

    public static function nodeArguments( NodeCompiler $node ) : array;

    public static function componentName() : string;

    public function templateNode( NodeCompiler $node ) : AuxiliaryNode;

    public function componentUniqueId() : string;

    public function render() : ?string;
}
