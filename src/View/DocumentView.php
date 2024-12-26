<?php

declare( strict_types = 1 );

namespace Core\View;

use Core\Http\Response\Document;
use Core\Service\AssetManager;
use Core\View\Component\Attributes;
use Core\Symfony\DependencyInjection\{ServiceContainer, ServiceContainerInterface};
use Support\Str;
use InvalidArgumentException;
use Throwable;
use function Support\toString;

final class DocumentView implements ServiceContainerInterface
{
    use ServiceContainer;

    /** @var string[] */
    private array $head = [];

    /** @var string[] */
    private array $content = [];

    /**
     * @param Document      $document
     * @param AssetManager  $assetManager
     */
    public function __construct(
            private readonly Document     $document,
            private readonly AssetManager $assetManager,
    ) {}

    /**
     * Assign `$this->content` from provided `$content`.
     *
     * @param ...$content
     *
     * @return self
     */
    public function setInnerContent( ...$content ) : self
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
                $message = 'The ' . __METHOD__ . '( ... $content ) only accepts string|string[]. ' . $e->getMessage();
                throw new InvalidArgumentException( $message );
            }
        }
        return $this;
    }

    public function renderContentHtml() : string
    {
        return <<<CONTENT
            {$this->head()}
            {$this->innerHtml()}
            CONTENT;
    }

    public function renderDocumentHtml() : string
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

    // :: Generate Document Meta

    public function meta( string $name, ?string $comment = null ) : self
    {
        if ( !$value = $this->document->pull( $name ) ) {
            return $this;
        }

        if ( $comment ) {
            $this->head[] = '<!-- ' . $comment . ' -->';
        }

        // dump(
        //         $this->document,
        //         $name,
        //         $value);

        $meta = \is_array( $value ) ? $value : [ $name => $value ];

        foreach ( $meta as $name => $value ) {
            if ( $value = toString( $value ) ) {
                $name         = Str::after( $name, '.' );
                $this->head[] = match ( $name ) {
                    'title' => $this->metaTitle( $value ),
                    default => "<meta name=\"{$name}\" content=\"{$value}\">",
                };
            }
        }

        return $this;
    }

    public function assets() : self
    {
        foreach ( $this->document->getEnqueuedAssets() as $type => $queue ) {
            foreach ( $queue as $name => $asset ) {
                try {

                    $asset        = $this->assetManager->get( $asset );
                    $this->head[] = (string) $asset->getHTML();
                } catch ( Throwable $e ) {

                }
            }
        }

        return $this;
    }

    // TODO : Title, Description, Keywords, Author, etc - separate service?
    private function metaTitle( ?string $value ) : string
    {
        // $value ??= $this->settings()->get( 'site.name', $_SERVER['SERVER_NAME'] );
        $value ??= 'no title';

        return "<title>{$value}</title>";
    }

    // :: End

    /**
     * @return string `<html ...>`
     */
    private function html() : string
    {
        $attributes = $this->document->pull( 'html' );

        \assert( \is_array( $attributes ) || \is_null( $attributes ) );

        if ( $attributes ) {
            $attributes = (string) new Attributes( $attributes );
        }

        return 'html' . ( $attributes ? ' ' . $attributes : '' );
    }

    /**
     * @return string `<body ...>`
     */
    private function body() : string
    {
        $attributes = $this->document->pull( 'body' );

        \assert( \is_array( $attributes ) || \is_null( $attributes ) );

        if ( $attributes ) {
            $attributes = (string) new Attributes( $attributes );
        }

        return 'body' . ( $attributes ? ' ' . $attributes : '' );
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

        dump( $this->head );
        foreach ( $this->head as $value ) {
            $html .= '    ' . $value . PHP_EOL;
        }

        return $html;
    }

    public function enqueueInvokedAssets( ?ComponentFactory $componentFactory = null ) : self
    {
        $componentFactory ??= $this->serviceLocator( ComponentFactory::class );

        $assets = $componentFactory->getInstantiated();
        dump( $assets );
        // $this->document->assets();

        return $this;
    }
}
