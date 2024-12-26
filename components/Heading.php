<?php

declare(strict_types=1);

namespace Core\View\Component;

use Core\View\Attribute\ViewComponent;
use Core\View\{Component};
// use Core\View\Template\TemplateCompiler;
use Northrook\HTML\Element\Tag;

// #[ViewComponent( Tag::HEADING, true, 128 )]
final class Heading
{
    public string $get;

    public string $tag = 'h1';
    //
    // protected function parseArguments( array &$arguments ) : void
    // {
    //     dump( $arguments );
    // }
    //
    // protected function compile( TemplateCompiler $compiler ) : string
    // {
    //     return $compiler->render( __DIR__.'/heading.latte', $this, cache : false );
    // }
}
