<?php

declare(strict_types=1);

namespace Core\View\Component;

use Core\View\Attribute\ViewComponent;
use Core\View\{Component, IconService};
use Core\View\Template\TemplateCompiler;

#[ViewComponent( 'icon:{get}', true, 128 )]
final class Icon extends Component
{
    protected function compile( TemplateCompiler $compiler ) : string
    {
        // TODO: Implement compile() method.
    }

}
