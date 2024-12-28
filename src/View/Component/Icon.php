<?php

declare(strict_types=1);

namespace Core\View\Component;

use Core\Service\IconService;
use Core\View\Attribute\ViewComponent;
use Northrook\Logger\Log;
use Core\View\Html\{Tag};

#[ViewComponent( 'icon:{get}', true, 128 )]
final class Icon extends AbstractComponent
{
    protected ?string $get;

    protected readonly Tag $tag;

    public function __construct(
        private readonly IconService $iconService,
    ) {
        $this->tag = Tag::from( 'i' );
    }

    protected function render() : string
    {
        if ( ! $this->get ) {
            Log::error( $this::class.': No icon key provided.' );
            return '';
        }

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
