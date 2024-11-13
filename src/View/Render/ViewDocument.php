<?php

namespace Core\View\Render;

final class ViewDocument extends View
{
    protected function build() : string
    {
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
