<?php

declare(strict_types=1);

namespace Core\View\Latte\Node;

use Latte\{Compiler};
use Latte\Compiler\{PrintContext, Tag};
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Runtime\HtmlStringable;
use Generator;

/**
 * Parsing `n:class` attributes for the {@see  Compiler\TemplateParser}.
 *
 * @copyright David Grudl
 * @see       https://davidgrudl.com  David Grudl
 * @see       https://latte.nette.org Latte Templating Engine
 *
 * @version   1.0 ✅
 * @author    Martin Nielsen <mn@northrook.com>
 *
 * @link      https://github.com/northrook Documentation
 * @todo      Update URL to documentation
 */
final class InlineStringableNode extends StatementNode
{
    public ArrayNode $args;

    public readonly ?string $renderedString;

    /**
     * @param Tag $tag
     *
     * @return InlineStringableNode
     */
    public static function create( Tag $tag ) : InlineStringableNode
    {
        $node       = new InlineStringableNode();
        $node->args = $tag->parser->parseArguments();

        $callable = \trim( $tag->parser->text, " \n\r\t\v\0()" );

        if ( \is_callable( $callable )
             && $called = ( $callable )() instanceof HtmlStringable
        ) {
            $node->renderedString = (string) ( $callable )();
        }
        else {
            $node->renderedString = null;
        }

        return $node;
    }

    public function print( PrintContext $context ) : string
    {
        return $context->format(
            'echo \''.$this->renderedString.'\' %line;',
            $this->position,
        );
    }

    public function &getIterator() : Generator
    {
        yield $this->args;
    }
}
