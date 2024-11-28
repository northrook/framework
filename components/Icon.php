<?php

declare(strict_types=1);

namespace Core\View\Component;

use Core\View\Attribute\ViewComponent;
use Core\View\{Component};
use Core\View\Template\TemplateCompiler;

#[ViewComponent( 'icon:{get}', true, 128 )]
final class Icon extends Component
{
    public string $get;

    protected function compile( TemplateCompiler $compiler ) : string
    {
        return $compiler->render( __DIR__.'/icon.latte', $this, cache : false );
    }
}
