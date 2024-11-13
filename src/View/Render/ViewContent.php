<?php

namespace Core\View\Render;

final class ViewContent extends View
{
    protected function build() : string
    {
        $this
            ->meta( 'document' )
            ->meta( 'meta' )
            ->assets( 'font' )
            ->assets( 'script' )
            ->assets( 'style' )
            ->assets( 'link' );

        return <<<CONTENT
            {$this->head()}
            {$this->innerHtml()}
            CONTENT;
    }
}
