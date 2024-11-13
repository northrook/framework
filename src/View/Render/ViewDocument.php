<?php

namespace Core\View\Render;

final class ViewDocument extends View
{
    protected function build() : string
    {
        $this
            ->meta( 'meta.viewport' )
            ->meta( 'document' )
            ->meta( 'robots' )
            ->meta( 'meta' )
            ->assets( 'font' )
            ->assets( 'script' )
            ->assets( 'style' )
            ->assets( 'link' );

        return <<<DOCUMENT
            <!DOCTYPE html>
            <{$this->html()}>
            <head>
                {$this->head()}
            </head>
            <{$this->body()}>
                {$this->innerHtml()}
            </body>
            </html>
            DOCUMENT;
    }
}
