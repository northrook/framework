<?php

declare(strict_types=1);

namespace Core\View;

use Core\Framework\Response\Document;
use Core\View\Component\Attributes;
use Core\Symfony\DependencyInjection\{ServiceContainer, ServiceContainerInterface};
use Symfony\Component\DependencyInjection\ServiceLocator;
use InvalidArgumentException;
use Throwable;

final class DocumentView implements ServiceContainerInterface
{
    use ServiceContainer;

    /** @var string[] */
    private array $head = [];

    /** @var string[] */
    private array $content = [];

    /**
     * @param Document       $document
     * @param ServiceLocator $serviceLocator
     * @param array|string   ...$content
     */
    public function __construct(
        private readonly Document         $document,
        protected readonly ServiceLocator $serviceLocator,
        array|string                   ...$content,
    ) {
        $this
            ->setInnerContent( ...$content )
            ->enqueueInvokedAssets();
    }

    public function contentHtml() : string
    {
        return <<<CONTENT
            {$this->head()}
            {$this->innerHtml()}
            CONTENT;
    }

    public function documentHtml() : string
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

    public function innerHtml( string $separator = '' ) : string
    {
        return \implode( $separator, $this->content );
    }

    /**
     * @return string `<html ...>`
     */
    private function html() : string
    {
        $attributes = $this->document->pull( 'html', null );

        \assert( \is_array( $attributes ) || \is_null( $attributes ) );

        if ( $attributes ) {
            $attributes = (string) new Attributes( $attributes );
        }

        return 'html'.( $attributes ? ' '.$attributes : '' );
    }

    /**
     * @return string `<body ...>`
     */
    private function body() : string
    {
        $attributes = $this->document->pull( 'body', null );

        \assert( \is_array( $attributes ) || \is_null( $attributes ) );

        if ( $attributes ) {
            $attributes = (string) new Attributes( $attributes );
        }

        return 'body'.( $attributes ? ' '.$attributes : '' );
    }

    /**
     * ```
     * <head>
     *     ...
     *     ...
     * </head>
     * ```
     *
     * @return string
     */
    private function head() : string
    {
        $html = '';

        foreach ( $this->head as $name => $value ) {
            $html .= '    '.$value.PHP_EOL;
        }

        return $html;
    }

    /**
     * Assign `$this->content` from provided `$content`.
     *
     * @param ...$content
     *
     * @return self
     */
    private function setInnerContent( ...$content ) : self
    {
        foreach ( $content as $html ) {
            try {
                if ( \is_string( $html ) ) {
                    $this->content[] = $html;
                }
                else {
                    $this->content = \array_merge( $this->content, $html );
                }
            }
            catch ( Throwable $e ) {
                $message = 'The '.__METHOD__.'( ... $content ) only accepts string|string[]. '.$e->getMessage();
                throw new InvalidArgumentException( $message );
            }
        }
        return $this;
    }

    private function enqueueInvokedAssets() : void
    {
        // TODO ::
        $assets = $this->serviceLocator( ComponentFactory::class )->getInstantiated();
        dump( $assets );
        // $this->document->assets();
    }
}
