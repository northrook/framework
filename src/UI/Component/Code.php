<?php

namespace Core\UI\Component;

use Core\View\Attribute\ComponentNode;
use Core\View\Component\ComponentBuilder;
use Core\View\Render\HtmlContent;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Northrook\Logger\Log;
use Support\Str;
use Tempest\Highlight\Highlighter;
use const Support\{EMPTY_STRING, WHITESPACE};
use Exception;

#[ComponentNode( [ 'pre', 'code:{language}:block' ], 'static' )]
final class Code extends ComponentBuilder
{
    protected const ?string TAG = 'code';

    protected bool $tidy = false;

    protected ?string $language = null;

    private bool $block = false;

    private string $code;

    protected function parseArguments( array &$arguments ) : void
    {
        if ( $arguments[ 'tag' ] === 'pre' ) {
            // $arguments[ 'block' ] = true;
        }
        $content = $arguments[ 'content' ] ?? [];

        if ( !\array_is_list( $content ) ) {
            $content = HtmlContent::contentArray( $content );
        }
        foreach ( $content as $index => $value ) {
            if ( \is_array( $value ) ) {
                // $value = HtmlContent::contentString( [$value]);
                // dump( [ $value ] );
                continue;
            }

            if ( !\is_string( $value ) ) {
                dump( __METHOD__ . ' encountered invalid content value.', $this, '---' );

                continue;
            }

            if ( !\trim( $value ) ) {
                unset( $content[ $index ] );
            }
        }
        $this->code = \implode( '', $content );

        unset( $arguments[ 'content' ] );
    }

    private function inlineCode() : void
    {
        $this->code = \preg_replace( '#\s+#', WHITESPACE, $this->code );
    }

    protected function block() : void
    {
        $leftPadding = [];
        $lines       = \explode( "\n", $this->code );

        foreach ( $lines as $line ) {
            $line = \str_replace( "\t", '    ', $line );
            if ( \preg_match( '#^(\s+)#m', $line, $matches ) ) {
                $leftPadding[] = \strlen( $matches[ 0 ] );
            }
        }

        $trimSpaces = \min( $leftPadding );

        foreach ( $lines as $line => $string ) {
            if ( \str_starts_with( $string, \str_repeat( ' ', $trimSpaces ) ) ) {
                $string = \substr( $string, $trimSpaces );
            }

            \preg_match( '#^(\s*)#m', $string, $matches );
            $leftPad        = \strlen( $matches[ 0 ] ?? 0 );
            $string         = \str_repeat( ' ', $leftPad ) . \trim( $string );
            $lines[ $line ] = \str_replace( '    ', "\t", $string );
        }

        $this->code = \implode( "\n", $lines );
        $this->component->tag( 'pre' )
                        ->class( 'block', prepend : true );
        $this->block = true;
    }

    protected function compile() : string
    {
        if ( !$this->block ) {
            $this->inlineCode();
        }

        if ( $this->tidy ) {
            $this->code = Str::replaceEach(
                    [ ' ), );' => ' ) );' ],
                    $this->code,
            );
        }

        if ( $this->language ) {
            $content = "{$this->highlight( $this->code )}";
            $lines   = \substr_count( $content, PHP_EOL );
            $this->component->attributes( 'language', $this->language );

            if ( $lines ) {
                $this->component->attributes( 'line-count', (string) $lines );
            }
        }
        else {
            $content = $this->code;
        }

        // dump( $content );
        $this->component->content( $content );
        // dump( $this );
        return (string) $this->component;
    }

    final protected function highlight( string $code, ?int $gutter = null ) : string
    {
        if ( !$this->language || !$code ) {
            return EMPTY_STRING;
        }

        if ( !$this->block && $gutter ) {
            Log::warning( 'Inline code snippets cannot have a gutter' );
            $gutter = null;
        }

        $highlighter = new Highlighter();
        if ( $gutter ) {
            return $highlighter->withGutter( $gutter )->parse( $code, $this->language );
        }
        return $highlighter->parse( $code, $this->language );
    }

    public function templateNode( NodeCompiler $node ) : AuxiliaryNode
    {
        return Render::templateNode(
                self::componentName(),
                $this::nodeArguments( $node ),
        );
    }

    public static function nodeArguments( NodeCompiler $node ) : array
    {
        return [
                'tag'        => $node->tag,
                'attributes' => $node->attributes(),
                'content'    => $node->parseContent(),
        ];
    }
}
