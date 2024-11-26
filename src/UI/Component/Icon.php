<?php

namespace Core\UI\Component;

use Core\View\Attribute\ViewComponent;
use Core\View\{Component, IconRenderer, Template\TemplateCompiler};
use Latte\Runtime\Html;

#[ViewComponent( 'icon:{get}', true, 128 )]
final class Icon extends Component
{
    protected const ?string TAG = 'i';

    protected string $get;

    public readonly Html $icon;

    public function __construct( public IconRenderer $iconRenderer )
    {
    }

    protected function compile( TemplateCompiler $compiler ) : string
    {
        $this->icon = new Html( $this->iconRenderer->iconPack->get( $this->get ) );
        return $compiler->render( __DIR__.'/icon.latte', $this, cache : false );
    }
}
