<?php

declare(strict_types=1);

namespace Core\View\Component;

use Core\Service\IconService;
use Core\View\Attribute\ViewComponent;

#[ViewComponent( 'icon:{get}', true, 128 )]
final class Icon extends AbstractComponent
{
    public function __construct(
        private readonly IconService $iconService,
    ) {
        dump( $this );
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
