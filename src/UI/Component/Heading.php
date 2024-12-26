<?php

namespace Core\UI\Component;

use Core\View\Attribute\ViewComponent;
use Core\View\Component;
use Core\View\Render\HtmlContent;
use Core\View\Template\TemplateCompiler;
use Northrook\HTML\Element\Tag;
use Northrook\HTML\HtmlNode;
use Support\Str;
use function String\stripTags;
use function Support\toString;
use const Support\WHITESPACE;

// #[ViewComponent( Tag::HEADING, true )]
final class Heading extends Component
{
    use Component\InnerContent;

    private string $heading;

    private string $level;

    private ?string $subheading = null;

    private bool $subheadingBefore = false;

    private bool $hGroup = false;

    public function subheading( ?string $string, bool $before = false, ?bool $hGroup = null ) : Heading
    {
        $this->subheading       = \trim( $string );
        $this->subheadingBefore = $before;
        if ( null !== $hGroup ) {
            $this->hGroup = $hGroup;
        }
        return $this;
    }

    public function getHeadingText() : string
    {
        if ( $this->hGroup ) {
            return stripTags( $this->heading );
        }

        $content = $this->subheadingBefore
                ? [$this->subheading, $this->heading]
                : [$this->heading, $this->subheading];

        return stripTags( toString( $content, ' ' ) );
    }

    protected function parseArguments( array &$arguments ) : void
    {
        $this->level = $arguments['tag'];
        $heading     = $arguments['content'] ?? null;

        if ( null === $heading ) {
            return;
        }

        unset( $arguments['content'] );

        if ( \is_array( $heading ) ) {
            $heading = HtmlContent::toArray( $heading );

            foreach ( $heading as $key => $value ) {
                if ( Str::startsWith( $key, ['small', 'p'] ) ) {
                    $this->subheading( $value, \array_key_first( $heading ) === $key );
                    unset( $heading[$key] );
                }
            }
        }

        $heading = toString( $heading, WHITESPACE );

        $heading = HtmlNode::unwrap( $heading, 'span' );

        $this->heading = $heading;
    }

    protected function compile( TemplateCompiler $compiler ) : string
    {
        if ( $this->hGroup ) {
            $this->tag->set( 'hgroup' );
        }

        // $this->attributes->add( 'id', $this->getHeadingText() );

        $this->heading
                = $this->hGroup ? "<{$this->level}>{$this->heading}</{$this->level}>" : "<span>{$this->heading}</span>";

        $this->content->append( $this->heading );

        if ( $this->subheadingBefore ) {
            $this->content->prepend( $this->subheading );
        }
        else {
            $this->content->append( $this->subheading );
        }

        return $compiler->render( __DIR__.'/heading.latte', $this, cache : false );
    }
}
