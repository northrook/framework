<?php

namespace Core\UI\Component;

use Core\UI\Component;
use Core\View\Attribute\ComponentNode;
use Core\View\Component\ComponentInterface;
use Core\View\Template\Compiler\NodeCompiler;
use Core\View\Template\Render;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Psr\Log\LoggerInterface;
use ValueError;

#[ComponentNode( ['a', 'a:primary', 'a:underline'] )]
final class Anchor extends Component
{
    public function __construct(
        string           $href,
        array            $attributes = [],
        array|string     $content = [],
        string           $tag = 'a',
        ?string          $uniqueId = null,
        ?LoggerInterface $logger = null,
    ) {
        $attributes['href'] = $href;
        parent::__construct( $tag, $attributes, $content, $uniqueId, $logger );
    }

    public static function compile(
        array            $arguments,
        array            $autowire = [],
        ?string          $uniqueId = null,
        ?LoggerInterface $logger = null,
    ) : ComponentInterface {
        $href       = $arguments['href']       ?? throw new ValueError( 'The [a href] value is required.' );
        $attributes = $arguments['attributes'] ?? [];
        $content    = $arguments['content']    ?? '';
        $tag        = $arguments['tag']        ?? 'a';

        unset( $arguments );

        return new Anchor(
            $href,
            $attributes,
            $content,
            $tag,
            $uniqueId,
            $logger,
        );
    }

    /**
     * @param ?string $set
     *
     * @return $this
     */
    public function setHref( ?string $set = null ) : self
    {
        $set ??= $this->element->attributes->pull( 'href' ) ?? '#';

        if ( '#' === $set ) {
            $this->logger->notice(
                'The {tag} component has {attribute} set to {href}.',
                [
                    'tag'       => $this->element->tag,
                    'attribute' => 'href',
                    'href'      => $set,
                ],
            );
        }

        // TODO : Validate schema://example.com
        // TODO : parse mailto:, tel:, sms:, etc
        // TODO : handle executable prefix javascript:url.tld
        // TODO : hreflang
        // TODO : sniff rel=referrerPolicy
        // TODO : sniff _target
        // TODO : sniff type
        // TODO : sniff name|id

        $this->attributes->set( 'href', $set );
        return $this;
    }

    protected function primary() : void
    {
        $this->element->class( 'primary' );
    }

    protected function underline() : void
    {
        $this->element->class( 'underline' );
    }

    protected function build() : string
    {
        $this->setHref();

        return (string) $this->element;
    }

    public static function templateNode( NodeCompiler $node ) : AuxiliaryNode
    {
        $attributes = $node->attributes();

        $href = $node->arguments()['href'] ?? $attributes['href'] ?? null;

        unset( $attributes['href'] );

        return Render::auxiliaryNode(
            self::componentName(),
            [
                'href'       => $href,
                'attributes' => $node->attributes(),
                'content'    => $node->parseContent(),
                'tag'        => $node->tag,
            ],
        );
    }
}
