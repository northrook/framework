<?php

namespace Core\UI\Component;

use Core\UI\Attribute\TemplateNode;
use Core\View\Component\ComponentBuilder;
use Core\View\Render\HtmlContent;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Northrook\HTML\Element\Tag;
use Northrook\HTML\HtmlNode;
use Support\Str;
use function String\stripTags;
use function Support\toString;
use const Support\WHITESPACE;

#[TemplateNode( Tag::HEADING, 'static' )]
final class Heading extends ComponentBuilder
{
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
            $heading = HtmlContent::contentArray( $heading );

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

    protected function compile() : string
    {
        if ( $this->hGroup ) {
            $this->component->tag( 'hgroup' );
        }

        $this->attributes->add( 'id', $this->getHeadingText() );

        $this->heading
                = $this->hGroup ? "<{$this->level}>{$this->heading}</{$this->level}>" : "<span>{$this->heading}</span>";

        $this->component
            ->content( $this->heading )
            ->content( $this->subheading, $this->subheadingBefore );

        return (string) $this->component;
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
        foreach ( $node->iterateChildNodes() as $key => $childNode ) {
            if ( $childNode instanceof ElementNode && \in_array( $childNode->name, ['small', 'p'] ) ) {
                $classes = $childNode->getAttribute( 'class' );

                $childNode->attributes->append(
                    $node::attributeNode(
                        'class',
                        [
                            'subheading',
                            $classes,
                        ],
                    ),
                );

                continue;
            }
        }
        return [
            'tag'        => $node->tag,
            'attributes' => $node->attributes(),
            'content'    => $node->parseContent(),
        ];
    }
}
