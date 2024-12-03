<?php

declare(strict_types=1);

namespace Core\Service\DesignSystem\StyleFramework;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Color extends AtomicRule
{
    protected const array BASELINE = [
        '--baseline-100' => '#050506',
        '--baseline-200' => '#0c0c0e',
        '--baseline-300' => '#131416',
        '--baseline-400' => '#1d1e20',
        '--baseline-500' => '#97989b',
        '--baseline-600' => '#8a8b8f',
        '--baseline-700' => '#eaeaeb',
        '--baseline-800' => '#f2f2f3',
        '--baseline-900' => '#fafafa',
    ];

    protected const array PRIMARY = [
        '--primary-100' => '#00030a',
        '--primary-200' => '#000819',
        '--primary-300' => '#000c28',
        '--primary-400' => '#01133c',
        '--primary-500' => '#3a73f8',
        '--primary-600' => '#2663f3',
        '--primary-700' => '#d9e4fc',
        '--primary-800' => '#e8eefd',
        '--primary-900' => '#f6f8fe',
    ];

    protected const array SUCCESS = [
        '--success-darkest'  => '#172620',
        '--success-darker'   => '#20563f',
        '--success-dark'     => '#227c57',
        '--success'          => '#4bce97',
        '--success-light'    => '#a7e7cc',
        '--success-lighter'  => '#c7f0df',
        '--success-lightest' => '#f3fcf8',
    ];

    protected const array INFO = [
        '--info-darkest'  => '#050505',
        '--info-darker'   => '#1b2432',
        '--info-dark'     => '#203a60',
        '--info'          => '#579dff',
        '--info-light'    => '#adcfff',
        '--info-lighter'  => '#e0edff',
        '--info-lightest' => '#f5f9ff',
    ];

    protected const array NOTICE = [
        '--notice-darkest'  => '#050505',
        '--notice-darker'   => '#1a1726',
        '--notice-dark'     => '#292056',
        '--notice'          => '#9f8fef',
        '--notice-light'    => '#c9c1f6',
        '--notice-lighter'  => '#f4f2fd',
        '--notice-lightest' => '#f7f6fe',
    ];

    protected const array WARNING = [
        '--warning-darkest'  => '#14130f',
        '--warning-darker'   => '#433a1e',
        '--warning-dark'     => '#6f5d1f',
        '--warning'          => '#f5cd47',
        '--warning-light'    => '#fae6a3',
        '--warning-lighter'  => '#fcf2cf',
        '--warning-lightest' => '#fefcf5',
    ];

    protected const array DANGER = [
        '--danger-darkest'  => '#050505',
        '--danger-darker'   => '#321c1b',
        '--danger-dark'     => '#602420',
        '--danger'          => '#f87268',
        '--danger-light'    => '#fbb6b1',
        '--danger-lighter'  => '#fee4e2',
        '--danger-lightest' => '#fff6f5',
    ];

    protected function variables() : array
    {
        return [
            ...$this::BASELINE,
            ...$this::PRIMARY,
            ...$this::SUCCESS,
            ...$this::INFO,
            ...$this::NOTICE,
            ...$this::WARNING,
            ...$this::DANGER,
        ];
    }

    protected function rules() : array
    {
        return [
            'baseline-100' => ['color' => '--baseline-900', 'background-color' => '--baseline-100'],
            'baseline-200' => ['color' => '--baseline-800', 'background-color' => '--baseline-200'],
            'baseline-300' => ['color' => '--baseline-700', 'background-color' => '--baseline-300'],
            'baseline-400' => ['color' => '--baseline-600', 'background-color' => '--baseline-400'],
            'baseline-500' => ['color' => '--baseline-500', 'background-color' => '--baseline-500'],
            'baseline-600' => ['color' => '--baseline-400', 'background-color' => '--baseline-600'],
            'baseline-700' => ['color' => '--baseline-300', 'background-color' => '--baseline-700'],
            'baseline-800' => ['color' => '--baseline-200', 'background-color' => '--baseline-800'],
            'baseline-900' => ['color' => '--baseline-100', 'background-color' => '--baseline-900'],
            'color-100'    => ['color' => '--baseline-100'],
            'color-200'    => ['color' => '--baseline-200'],
            'color-300'    => ['color' => '--baseline-300'],
            'color-400'    => ['color' => '--baseline-400'],
            'color-500'    => ['color' => '--baseline-500'],
            'color-600'    => ['color' => '--baseline-600'],
            'color-700'    => ['color' => '--baseline-700'],
            'color-800'    => ['color' => '--baseline-800'],
            'color-900'    => ['color' => '--baseline-900'],
            'bg-100'       => ['background-color' => '--baseline-100'],
            'bg-200'       => ['background-color' => '--baseline-200'],
            'bg-300'       => ['background-color' => '--baseline-300'],
            'bg-400'       => ['background-color' => '--baseline-400'],
            'bg-500'       => ['background-color' => '--baseline-500'],
            'bg-600'       => ['background-color' => '--baseline-600'],
            'bg-700'       => ['background-color' => '--baseline-700'],
            'bg-800'       => ['background-color' => '--baseline-800'],
            'bg-900'       => ['background-color' => '--baseline-900'],
        ];
    }
}
