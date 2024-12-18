<?php

declare(strict_types=1);

namespace Core\Service\DesignSystem;

use Core\Service\DesignSystem\StyleFramework\{AtomicRule, Color, Display, Variables};
use Support\{Filesystem, Str};
final class StyleFramework
{
    /**
     * @noinspection CssUnusedSymbol
     * @noinspection CssUnresolvedCustomProperty
     */
    public const string BASELINE = <<<'CSS'
        html {
          --background: var(--baseline-800);
          --color: var(--baseline-200);
          font-family : var(--font-body), system-ui;
          font-size : var(--text-body);
        }
        p, .heading, .h1, .h2, .h3, .h4 {
          overflow-wrap : break-word;
          text-wrap     : pretty;
        }
        .heading {
          --color: var(--primary-500);
          font-family   : var(--font-heading), sans-serif;
          font-weight   : var(--weight-heading);
        }
        h1, .h1 {
          font-size : var(--text-h1);
        }
        h2, .h2 {
          font-size : var(--text-h2);
        }
        h3, .h3 {
          font-size : var(--text-h3);
        }
        h4, .h4 {
          font-size : var(--text-h4);
        }
        small {
          font-size : var(--text-small);
        }
        .content > * + * {
          margin-top : var(--line-height);
        }
        .nowrap {
          white-space : nowrap;
        }
        code, pre {
          tab-size : 4ch;
        }
        [role=list] {
          display        : flex;
          flex-direction : column;
        }
        [role=list].right {
          align-items : flex-end;
        }
        [role=list].reverse {
          flex-direction : column-reverse;
        }
        svg.icon {
          height : var(--size, 1em);
          width  : var(--size, 1em);
        }
        svg.icon.direction\:up {
          rotate : 0deg;
        }
        svg.icon.direction\:right {
          rotate : 90deg;
        }
        svg.icon.direction\:down {
          rotate : 180deg;
        }
        svg.icon.direction\:left {
          rotate : 270deg;
        }
        .gap {
          gap: var(--gap-row) var(--gap-col);
        }
        .gap-row {
          row-gap: var(--gap-row);
        }
        .gap-col {
          column-gap: var(--gap-col);
        }
        .sr-only {
            position     : absolute;
            width        : 1px;
            height       : 1px;
            padding      : 0;
            margin       : -1px;
            overflow     : hidden;
            clip         : rect(0, 0, 0, 0);
            white-space  : nowrap;
            border-width : 0;
        }
        CSS;

    /** @var class-string<AtomicRule>[] */
    private const array GENERATORS = [
        Variables::class,
        Color::class, // Production uses HSL - 242 100 50, allowing for --opacity; The stub uses hex for IDE
        Display::class,
    ];

    private string $style;

    public function __construct()
    {
    }

    public function generateStub( string $savePath ) : void
    {
        Filesystem::save( $savePath, $this->style() );
    }

    public function generate( ?string $atomicRule = null ) : self
    {
        /** @var array<string, array<string, string>|string> $rules */
        $rules = [':root' => []];

        foreach ( $this::GENERATORS as $atomic ) {
            $rules = \array_merge_recursive( $rules, $atomic::generate() );
        }

        foreach ( $rules as $selector => $declarations ) {
            $rules[$selector] = $this->rule( $selector, $declarations );
        }

        $rules[] = $this::BASELINE;

        $this->style = \implode( PHP_EOL, $rules );

        return $this;
    }

    public function style() : string
    {
        return $this->style ?? $this->generate()->style;
    }

    private function selector( string $selector ) : string
    {
        return \trim( Str::escape( $selector, ':' ), " \n\r\t\v\0:" );
    }

    private function rule( string $selector, array $declarations ) : string
    {
        return \implode( PHP_EOL, ["{$selector} {", ...$this->declarations( $declarations ), '}'] );
    }

    private function declarations( array $declarations ) : array
    {
        foreach ( $declarations as $declaration => $value ) {
            if ( \str_starts_with( $value, '--' ) ) {
                $value = "var({$value})";
            }

            $declarations[$declaration] = "    {$declaration} : {$value};";
        }
        return $declarations;
    }

    final public static function escape( string $string ) : string
    {
        $escape = \str_replace( ':', '\:', $string );
        return $escape;
    }
}
