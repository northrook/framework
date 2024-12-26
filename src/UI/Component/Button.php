<?php

namespace Core\UI\Component;

use Core\Service\IconService;
use Core\View\Attribute\ViewComponent;
use Core\View\{Component, Template\TemplateCompiler};

// #[ViewComponent( ['button', 'button:submit'], true )]
final class Button extends Component
{
    use Component\InnerContent;

    protected const ?string TAG = 'button';

    public ?string $icon = null;

    public function __construct( private readonly IconService $iconRenderer )
    {
    }

    protected function parseArguments( array &$arguments ) : void
    {
        $this->icon = $arguments['icon'] ?? null;
        unset( $arguments['icon'] );
    }

    protected function compile( TemplateCompiler $compiler ) : string
    {
        if ( $this->icon ) {
            $this->icon = $this->iconRenderer->iconPack->get( $this->icon );
        }

        return $compiler->render( __DIR__.'/button.latte', $this, cache : false );
    }

    protected function submit() : void
    {
        // $this->attributes->set( 'type', 'submit' );
    }
}
