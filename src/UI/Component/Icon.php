<?php

namespace Core\UI\Component;

use Core\View\Attribute\ViewComponent;
use Core\View\{Component, IconRenderer, Template\TemplateCompiler};
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @method void build()
 */
#[ViewComponent( 'icon:{get}', true, 128 )]
final class Icon extends Component
{
    protected const ?string TAG = 'i';

    protected string $get;

    #[Required]
    public IconRenderer $iconRenderer;

    public readonly string $icon;

    protected function compile( TemplateCompiler $compiler ) : string
    {
        $this->icon = $this->iconRenderer->iconPack->get( $this->get );
        return $compiler->render( __DIR__.'/icon.latte', $this, cache : false );
    }
}
