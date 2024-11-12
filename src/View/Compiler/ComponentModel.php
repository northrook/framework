<?php

declare(strict_types=1);

namespace Core\View\Compiler;

use Core\View\Attribute\ComponentNode;
use Core\View\ComponentInterface;
use Exception\NotImplementedException;
use Support\{ClassInfo, Reflect};

final readonly class ComponentModel
{
    public readonly string $name;

    /** @var class-string<ComponentInterface> */
    public readonly string $class;

    /** @var string[] */
    public readonly array $tags;

    public function __construct( string|ClassInfo|ComponentInterface $register )
    {
        if ( ! $register instanceof ComponentInterface ) {
            $register = new ClassInfo( $register );
        }

        if ( ! $register->implements( ComponentInterface::class ) ) {
            throw new NotImplementedException( $register->class, ComponentInterface::class );
        }

        $this->class = $register->class;

        $componentNode = Reflect::getAttribute( $register->reflect(), ComponentNode::class );

        $this->name = $this->class::componentName();
        $this->tags = $componentNode->tags ?? [];
    }
}
