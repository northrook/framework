<?php

declare(strict_types=1);

namespace Core\Service\DesignSystem\StyleFramework;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Display extends AtomicRule
{
    protected function variables() : array
    {
        return [];
    }

    protected function rules() : array
    {
        /** look into aliasing 'none' in {@see \Core\View\Template\NodeParser::attributes()}*/
        return [
            'hidden'                => ['display' => 'none'],
            'block'                 => ['display' => 'block'],
            'inline'                => ['display' => 'inline'],
            'inline-block'          => ['display' => 'inline-block'],
            'inline-flex'           => ['display' => 'inline-flex'],
            'inline-grid'           => ['display' => 'inline-grid'],
            'flex'                  => ['display' => 'flex'],
            'flex.reverse'          => ['flex-direction' => 'row-reverse'],
            'flex.align-top'        => ['align-items' => 'flex-start'],
            'flex.align-right'      => ['align-items' => 'flex-end'],
            'flex.align-bottom'     => ['align-items' => 'flex-start'],
            'flex.align-left'       => ['align-items' => 'flex-start'],
            'flex.align-center'     => ['align-items' => 'flex-start'],
            'flex.col'              => ['flex-direction' => 'column'],
            'flex.col.reverse'      => ['flex-direction' => 'column-reverse'],
            'flex.col.align-top'    => ['justify-content' => 'flex-start'],
            'flex.col.align-right'  => ['justify-content' => 'flex-end'],
            'flex.col.align-bottom' => ['justify-content' => 'flex-start'],
            'flex.col.align-left'   => ['justify-content' => 'flex-start'],
            'flex.col.align-center' => ['justify-content' => 'flex-start'],
            'flow'                  => ['display' => 'flow-root'],
        ];
    }
}
