<?php

namespace Core\View\Component;

use Core\View\Template\Compiler\NodeCompiler;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Psr\Log\LoggerInterface;
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
     * @param array<array-key, mixed> $arguments
     * @param array<string, object>   $autowire
     * @param ?string                 $uniqueId
     * @param ?LoggerInterface        $logger
     *
     * @return ComponentInterface
     */
    public static function create(
        array            $arguments,
        array            $autowire = [],
        ?string          $uniqueId = null,
        ?LoggerInterface $logger = null,
    ) : ComponentInterface;

    public static function templateNode( NodeCompiler $node ) : AuxiliaryNode;

    public static function componentName() : string;

    public function componentUniqueId() : string;

    public function render() : ?string;
}
