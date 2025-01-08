<?php

declare(strict_types=1);

namespace Core\Service\DesignSystem\StyleFramework;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Variables extends AtomicRule
{
    protected const array DOCUMENT = [
        // The size of something
        'size' => '1em',
        // Spacing between items
        'gap'                => '1rem',
        'gap-row'            => '1rem',
        'gap-col'            => '1rem',
        'gutter'             => '2ch',    // left|right padding for elements
        'min-width'          => '20rem',  // 320px
        'max-width'          => '75rem', // 1200px
        'scroll-padding-top' => '--offset-top', // maybe +--gap?
        'offset-top'         => '1rem',
        'offset-left'        => '1rem',
        'offset-right'       => '1rem',
        'offset-bottom'      => '1rem',
    ];

    protected const array TYPOGRAPHY = [
        'font-body'    => 'Inter',
        'font-heading' => 'Times New Roman',
        'font-code'    => 'monospace',
        'line-height'  => '1.6em',
        'line-spacing' => '.5em', // spacing between inline elements
        'line-length'  => '64ch', // limits inline text elements, like p and h#

        'weight-body'    => '400',
        'weight-bold'    => '600',
        'weight-heading' => '500',

        'text-body'  => '1rem',
        'text-h1'    => 'min(max(1.8rem, 6vw), 3.05rem)',
        'text-h2'    => 'min(max(1.6rem, 6vw), 2rem)',
        'text-h3'    => 'min(max(1.25rem, 6vw), 1.8rem)',
        'text-h4'    => 'min(max(1.1rem, 6vw), 1.5rem)',
        'text-small' => '.875rem',
    ];

    protected const array BOX = [
        'radius-inline' => '.2em',
        'radius-box'    => '.5rem',
    ];

    // TODO : Generate dynamic sizes:
    // ?      w:16   -> style="--width:  16px"
    // ?      h:1rem -> style="--height: 1rem"
    // ?      h:5vh  -> style="--height: 5vh"
    protected const array SIZES = [
        // agnostic
        'auto' => 'auto',
        'none' => '0',
        'us'   => '.125rem', // 2px
        'xs'   => '.25rem',  // 4px
        'sm'   => '.5rem',
        'ms'   => '.75rem',
        'md'   => '1rem',    // 16px
        'ml'   => '1.5rem',  // 24px
        'lg'   => '2rem', // using em allows variable spacing based on font size
        'xl'   => '3rem',
    ];

    protected function variables() : array
    {
        return [
            ...$this::DOCUMENT,
            ...$this::TYPOGRAPHY,
            ...$this::SIZES,
        ];
    }

    protected function rules() : array
    {
        $sizes = [
        ];

        foreach ( $this::SIZES as $size => $unused ) {
            $value = match ( $size ) {
                'auto'  => 'auto',
                'none'  => '0',
                default => "--{$size}",
            };

            $sizes["space-h-{$size} > * + *"] = [
                '--mt'       => $value,
                'margin-top' => 'var(--mt)',
            ];

            $sizes["space-v-{$size} > * + *"] = [
                '--mt'        => $value,
                'margin-left' => 'var(--mt)',
            ];

            $sizes["m-{$size}"] = [
                '--m'    => $value,
                'margin' => 'var(--m)',
            ];
            $sizes["mt-{$size}"] = [
                '--mt'       => $value,
                'margin-top' => 'var(--mt)',
            ];
            $sizes["mr-{$size}"] = [
                '--mr'         => $value,
                'margin-right' => 'var(--mr)',
            ];
            $sizes["mb-{$size}"] = [
                '--mb'          => $value,
                'margin-bottom' => 'var(--mb)',
            ];
            $sizes["ml-{$size}"] = [
                '--ml'        => $value,
                'margin-left' => 'var(--ml)',
            ];
            $sizes["mh-{$size}"] = [
                '--mh'         => $value,
                'margin-left'  => 'var(--mh)',
                'margin-right' => 'var(--mh)',
            ];
            $sizes["mv-{$size}"] = [
                '--mv'          => $value,
                'margin-top'    => 'var(--mv)',
                'margin-bottom' => 'var(--mv)',
            ];
            $sizes["p-{$size}"] = [
                '--p'     => $value,
                'padding' => 'var(--p)',
            ];
            $sizes["pt-{$size}"] = [
                '--pt'        => $value,
                'padding-top' => 'var(--pt)',
            ];
            $sizes["pr-{$size}"] = [
                '--pr'          => $value,
                'padding-right' => 'var(--pr)',
            ];
            $sizes["pb-{$size}"] = [
                '--pb'           => $value,
                'padding-bottom' => 'var(--pb)',
            ];
            $sizes["pl-{$size}"] = [
                '--pl'         => $value,
                'padding-left' => 'var(--pl)',
            ];
            $sizes["ph-{$size}"] = [
                '--ph'          => $value,
                'padding-left'  => 'var(--ph)',
                'padding-right' => 'var(--ph)',
            ];
            $sizes["pv-{$size}"] = [
                '--pv'           => $value,
                'padding-top'    => 'var(--pv)',
                'padding-bottom' => 'var(--pv)',
            ];
        }

        return [
            'fixed'        => ['position' => 'fixed', 'z-index' => '125'],
            'fixed.top'    => ['top' => '--offset-top'],
            'fixed.right'  => ['right' => '--offset-right'],
            'fixed.bottom' => ['bottom' => '--offset-bottom'],
            'fixed.left'   => ['left' => '--offset-left'],
            'text:body'    => ['font-size' => '--text-body'],
            'text:h1'      => ['font-size' => '--text-h1'],
            'text:h2'      => ['font-size' => '--text-h2'],
            'text:h3'      => ['font-size' => '--text-h3'],
            'text:h4'      => ['font-size' => '--text-h4'],
            'text:small'   => [
                'font-size'     => '--text-small',
                '--line-height' => '1rem',
            ],
            ...$sizes,
        ];
    }
}
