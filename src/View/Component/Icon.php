<?php

declare(strict_types=1);

namespace Core\View\Component;

use Core\Service\IconService;
use Core\View\Attribute\ViewComponent;
use Core\View\Html\{Attributes, Tag};

#[ViewComponent( 'icon:{get}', true, 128 )]
final class Icon extends AbstractComponent
{
    protected string $get;

    protected readonly Tag $tag;

    protected readonly Attributes $attributes;

    public function __construct(
        private readonly IconService $iconService,
    ) {
        $this->tag        = Tag::from( 'i' );
        $this->attributes = new Attributes();
    }

    protected function render() : string
    {
        $iconHtml = $this->iconService->getIcon( $this->get );

        if ( ! $iconHtml ) {
            return '';
        }

        return <<<HTML
            <i{$this->attributes}>
            {$iconHtml}
            </i>
            HTML;
    }
}

// #[ViewComponent( 'icon:{get}', true, 128 )]
// final class Icon extends Component
// {
//     public string $get;
//
//     protected function compile( TemplateCompiler $compiler ) : string
//     {
//         return $compiler->render( __DIR__.'/icon.latte', $this, cache : false );
//     }
// }
