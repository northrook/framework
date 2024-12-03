<?php

namespace Core\View\Render;

use Core\Framework\Autowire\Settings;
use Core\Service\AssetLocator;
use Core\Symfony\DependencyInjection\ServiceContainer;
use Core\Framework\Response\Document;
use Core\View\ComponentFactory;
use Northrook\HTML\Element\Attributes;
use Northrook\Logger\Log;
use Support\Str;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Stringable;
use function Support\toString;

abstract class HtmlView implements Stringable
{
    use ServiceContainer, Settings;

    private array $notifications = [];

    private array $head = [];

    public function __construct(
            private readonly Document         $document,
            private string                    $content,
            protected readonly ServiceLocator $serviceLocator,
    ) {
    }

    abstract protected function build() : string;

    final public function render() : string
    {
        $this->resolveNotifications();
        $this->enqueueInvokedAssets();
        return $this->build();
    }

    final public function __toString() : string
    {
        return $this->render();
    }

    /**
     * @param 'body'|'html' $get
     *
     * @return string
     */
    private function attributes( string $get ) : string
    {
        $attributes = $this->document->pull( $get, null );

        if ( ! $attributes ) {
            return '';
        }

        $attributes = Attributes::from( $attributes )->toString();

        return $attributes ? " {$attributes}" : '';
    }

    /**
     * @return string `<html ...>`
     */
    final protected function html() : string
    {
        return 'html'.$this->attributes( 'html' );
    }

    /**
     * @return string `<body ...>`
     */
    final protected function body() : string
    {
        return 'body'.$this->attributes( 'body' );
    }

    final protected function head() : string
    {
        $html = '';

        foreach ( $this->head as $name => $value ) {
            $html .= '    '.$value.PHP_EOL;
        }
        return $html;
    }

    final protected function innerHtml( bool $compress = false ) : string
    {
        // TODO : Check if $this->content has or starts with a <body..>
        if ( ! empty( $this->notifications ) ) {
            $this->content = \implode( PHP_EOL, $this->notifications + [$this->content] );
        }

        if ( $compress ) {
            // TODO : Minify::HTML
            Log::alert( 'Implement {HTML} minification.' );
        }

        return $this->content;
    }

    // :: PARSE

    // TODO : Title, Description, Keywords, Author, etc - separate service?
    private function metaTitle( ?string $value ) : string
    {
        $value ??= $this->settings()->get( 'site.name', $_SERVER['SERVER_NAME'] );

        return "<title>{$value}</title>";
    }

    final protected function meta( string $name, ?string $comment = null ) : self
    {
        if ( ! $value = $this->document->pull( $name ) ) {
            return $this;
        }

        if ( $comment ) {
            $this->head[] = '<!-- '.$comment.' -->';
        }

        // dump(
        //         $this->document,
        //         $name,
        //         $value);

        $meta = \is_array( $value ) ? $value : [$name => $value];

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

    /**
     * @param null|'link'|'script'|'style' $type
     * @param ?string                      $id
     *
     * @return $this
     */
    final protected function assets( ?string $type = null, ?string $id = null ) : self
    {
        $locator = $this->serviceLocator( AssetLocator::class );
        $parse   = $type ? [$type] : ['script', 'style', 'link'];

        foreach ( $parse as $type ) {
            $asset = $this->document->pull( $type );

            dump( $asset );

            if ( ! $asset ) {
                continue;
            }

            $id     = $asset['id']     ?? null ? ' id="'.$asset['id'].'"' : '';
            $inline = $asset['inline'] ?? false;
            $path   = $locator->get( $asset['path'] . '.css' );
            dump($path);
            $contents = file_get_contents( $path['path'] );
            dump( $contents );

            $assign = '<style'.$id.'>'.$contents.'</style>';
            // $assign = match ( $type ) {
            //     // 'script' => ( function() use ( $asset, $id ) {
            //     //     return "<script{$id} src=\"{$asset['path']}\"></script>";
            //     // } )(),
            //     // 'style' => ( function() use ( $asset, $id ) {
            //     //     return "<link{$id} rel=\"stylesheet\" href=\"{$asset['path']}\"></link>";
            //     // } )(),
            //     // 'link' => ( function() use ( $asset, $id ) {
            //     //     return "<link{$id} href=\"{$asset['path']}\"></link>";
            //     // } )(),
            //     default => null,
            // };

            if ( $assign ) {
                $this->head[] = $assign;
            }
        }

        return $this;
    }

    final protected function style( ?string $id = null ) : self
    {
        return $this->assets( 'style', $id );
    }

    final protected function script( ?string $id = null ) : self
    {
        return $this->assets( 'script', $id );
    }

    // :: PARSE

    private function enqueueInvokedAssets() : void
    {
        // TODO ::
        $assets = $this->serviceLocator( ComponentFactory::class )->getInstantiated();
        dump( $assets );
        $this->document->assets();
    }

    private function resolveNotifications() : void
    {
        // foreach ( $this->serviceLocator( ToastService::class )->getMessages() as $id => $message ) {
        //     $this->notifications[$id] = new Notification(
        //             $message->type,
        //             $message->title,
        //             $message->description,
        //             $message->timeout,
        //     );
        //
        //     if ( ! $message->description ) {
        //         $this->notifications[$id]->attributes->add( 'class', 'compact' );
        //     }
        //
        //     if ( ! $message->timeout && 'error' !== $message->type ) {
        //         $this->notifications[$id]->setTimeout( 5_000 );
        //     }
        //
        //     $this->notifications[$id] = (string) $this->notifications[$id];
        // }
    }
}
