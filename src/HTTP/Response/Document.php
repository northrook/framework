<?php

declare( strict_types = 1 );

namespace Core\HTTP\Response;

use Core\Assets\Factory\Asset\Type;
use Psr\Log\LoggerInterface;
use Stringable;
use Support\Interface\ActionInterface;
use Support\Normalize;
use function Support\toString;

final class Document implements ActionInterface
{
    private const array GROUPS = [
            'document' => [ 'title', 'description', 'author', 'keywords' ],
            'theme'    => [ 'color', 'scheme', 'name' ],
    ];

    /** @var bool automatically locked when read. */
    private bool $locked = false;

    /** @var array<array<array-key,string>|string> */
    protected array $document = [];

    /** @var string[] */
    protected array $assets = [];

    /** @var bool Determines how robot tags will be set */
    public bool $isPublic = false;

    public function __construct(
            private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * @param null|string           $title
     * @param null|string           $description
     * @param null|string|string[]  $keywords
     * @param null|string           $author
     * @param null|string           $status
     *
     * @return $this
     */
    public function __invoke(
            ?string               $title = null,
            ?string               $description = null,
            null | string | array $keywords = null,
            ?string               $author = null,
            ?string               $status = null,
    ) : self
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }
        $set = \array_filter( \get_defined_vars() );

        foreach ( $set as $name => $value ) {
            $this->set( $name, $value );
        }

        return $this;
    }

    /**
     * @param 'document.author'|'document.description'|'document.keywords'|'document.title'|'html.id'|'html.lang'|string  $key
     * @param null|array|bool|int|string                                                                                  $value
     *
     * @return $this
     */
    public function add(
            string                             $key,
            null | string | int | bool | array $value,
    ) : self
    {
        $this->set( $key, $value, false );
        return $this;
    }

    public function html(
            ?string $class = null,
            ?string $status = null,
            ?string $id = null,
            ?string $locale = null,
            string  ...$attributes,
    ) : self
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }
        $set = \array_filter( [ 'html' => $id, 'class' => $class, 'status' => $status, ...$attributes ] );

        foreach ( $set as $name => $value ) {
            $this->set( "head.{$name}", $value );
        }
        return $this;
    }

    public function head( string $key, string | Stringable $html ) : Document
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }
        $value = $html instanceof Stringable ? $html->__toString() : $html;

        // TODO : Cache
        // TODO : Linting / validation step

        $this->set( 'head.' . Normalize::key( $key ), $value );

        return $this;
    }

    /**
     * Set an arbitrary meta tag.
     *
     * - This method does not validate the name or content.
     * - The name is automatically prefixed with the group if relevant.
     *
     * @param 'author'|'description'|'keywords'|'title'|string  $name
     * @param string                                            ...$content
     *
     * @return $this
     */
    public function meta( string $name, string ...$content ) : self
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }
        $this->set( $name, $content );

        return $this;
    }

    /**
     * @param string  $bot      = [ 'googlebot', 'bingbot', 'yandexbot'][$any]
     * @param string  ...$rule  = [
     *                          'index', 'noindex', 'follow', 'nofollow',
     *                          'index, follow', 'noindex, nofollow',
     *                          'noarchive', 'nosnippet', 'nositelinkssearchbox'
     *                          ][$any]
     *
     * @return Document
     *
     * @see https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag Documentation
     */
    public function robots( string $bot, string ...$rule ) : Document
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }
        $rules = [];

        foreach ( $rule as $content ) {
            if ( !\is_string( $content ) ) {
                $this->logger?->error(
                        message : 'Invalid robots rule for {bot}, a string is required, but {type} was provided.',
                        context : [ 'bot' => $bot, 'type' => \gettype( $content ) ],
                );

                continue;
            }

            if ( \str_contains( $content, ',' ) ) {
                foreach ( \explode( ',', $content ) as $value ) {
                    $rules[] = \trim( $value );
                }
            }
            else {
                $rules[] = \trim( $content );
            }
        }

        $this->set( "robots.{$bot}", \implode( ', ', $rules ) );

        return $this;
    }

    /**
     * Enqueue assets using their `name`.
     *
     * The {@see ResponseHandler} will ensure the requested assets are provided if needed.
     *
     * @param string  ...$enqueue
     *
     * @return $this
     */
    public function asset( string ...$enqueue ) : self
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }

        foreach ( $enqueue as $asset ) {
            if ( \str_contains( $asset, '.' ) ) {
                [ $type, $name ] = \explode( '.', $asset, 2 );
                $this->assets[ $type ][ $name ] = $asset;
            }
            else {
                $this->assets[ $asset ] = $asset;
            }
        }

        return $this;
    }

    /**
     * @param array<array-key, mixed>|string  $set
     *
     * @return $this
     */
    public function body( string | array ...$set ) : self
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return $this;
        }

        foreach ( $set as $name => $value ) {
            \assert( \is_string( $name ) );

            $separator = 'style' === $name ? ';' : ' ';
            $value     = match ( $name ) {
                'class', 'style' => \is_array( $value ) ? $value : \explode( $separator, $value ),
                default          => $value,
            };

            \assert( \is_string( $value ) );

            $this->set( "body.{$name}", $value );
        }
        return $this;
    }

    public function theme(
            string  $scheme = 'dark light',
            ?string $color = null,
            ?string $name = 'system',
    ) : self
    {
        // Needs to generate theme.scheme.color,
        // this is to allow for different colors based on light/dark

        foreach ( [
                'color'  => $color,
                'scheme' => $scheme,
                'name'   => $name,
        ] as $metaName => $content ) {
            $this->set( "theme.{$metaName}", $content );
        }

        return $this;
    }

    /**
     * @param string  $key
     *
     * @return null|string|string[]
     */
    public function get( string $key ) : null | string | array
    {
        return $this->document[ $key ] ?? null;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getAll() : array
    {
        return $this->document;
    }

    /**
     * @param string  $key
     *
     * @return null|string|string[]
     */
    public function pull( string $key ) : null | string | array
    {
        $pull = $this->document[ $key ] ?? null;

        if ( $pull ) {
            unset( $this->document[ $key ] );
        }

        return $pull;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function pullAll() : array
    {
        $pull = [];

        foreach ( $this->document as $key => $value ) {
            $pull[ $key ] = $this->pull( $key );
        }

        return \array_filter( $pull );
    }

    // style, link, script - force src/url (clearly denote these are EXTERNAL resources
    // ->assets(.. ) uses the AssetManager

    /**
     * @param string                                         $string
     * @param null|array<array-key, string>|bool|int|string  $value
     * @param bool                                           $override
     *
     * @return void
     */
    public function set(
            string                             $string,
            null | string | int | bool | array $value = null,
            bool                               $override = true,
    ) : void
    {
        if ( $this->isLocked( __METHOD__ ) ) {
            return;
        }

        $key = $this->key( $string );

        if ( !$override && \array_key_exists( $key, $this->document ) ) {
            return;
        }

        $value = match ( \gettype( $value ) ) {
            'boolean' => $value ? 'true' : 'false',
            'NULL'    => null,
            'array'   => $value,
            default   => (string) $value,
        };

        if ( null === $value ) {
            return;
        }

        $this->document[ $key ] = $value;
    }

    public function getEnqueuedAssets( ?Type $type = null ) : array
    {
        return $this->assets;
    }

    protected function key( string $string ) : string
    {
        $key = Normalize::key( $string, '-' );

        foreach ( $this::GROUPS as $group => $names ) {
            if ( \in_array( $key, $names ) ) {
                return "{$group}.{$key}";
            }
        }

        return $key;
    }

    /**
     * @param null|array<array-key, string>|bool|int|string  $value
     *
     * @return string
     */
    protected function value( null | string | int | bool | array $value = null ) : string
    {
        return toString( $value );
    }

    private function isLocked( string $method = __CLASS__ ) : bool
    {
        if ( !$this->locked ) {
            return false;
        }

        $this->logger?->error(
                'The {caller} is locked. No further changes can be made at this time.',
                [ 'caller' => $method, 'document' => $this ],
        );

        return true;
    }

    // /**
    //  * @param array<string,null|array<array-key, string>|bool|int|string>|string $add
    //  * @param null|array<array-key, string>|bool|int|string                      $value
    //  *
    //  * @return $this
    //  */
    // public function add( array|string $add, null|string|int|bool|array $value = null ) : Document
    // {
    //     if ( $this->isLocked() ) {
    //         return $this;
    //     }
    //
    //     // Allows setting multiple values
    //     if ( \is_array( $add ) ) {
    //         foreach ( $add as $key => $value ) {
    //             $this->add( $key, $value );
    //         }
    //
    //         return $this;
    //     }
    //     return parent::add( $add, $value );
    // }
    //
    // /**
    //  * @param array<string,null|array<array-key, string>|bool|int|string>|string $set
    //  * @param null|array<array-key, string>|bool|int|string                      $value
    //  *
    //  * @return $this
    //  */
    // public function set( array|string $set, null|string|int|bool|array $value = null ) : Document
    // {
    //     if ( $this->isLocked() ) {
    //         return $this;
    //     }
    //     // Allows setting multiple values
    //     if ( \is_array( $set ) ) {
    //         foreach ( $set as $key => $value ) {
    //             $this->set( $key, $value );
    //         }
    //     }
    //     else {
    //         $key = $this->key( $set );
    //     }
    //
    //     return $this;
    // }
}

// declare( strict_types = 1 );
//
// namespace Core\Framework\Response;
//
// use Northrook\{ArrayAccessor, Filesystem\Path, Logger\Log};
// use Stringable;
// use Support\Normalize;
// use function Support\toString;
// use InvalidArgumentException;
//
// /**
//  * Handles all Document related properties.
//  *
//  * @author Martin Nielsen <mn@northrook.com>
//  */
// final class Document extends ArrayAccessor
// {
//     private const array META_GROUPS = [
//             'html'     => [ 'id', 'status' ],
//             'document' => [ 'title', 'description', 'author', 'keywords' ],
//             'theme'    => [ 'color', 'scheme', 'name' ],
//     ];
//
//     protected bool $locked = false;
//
//     /** @var bool Determines how robot tags will be set */
//     public bool $isPublic = false;
//
//     private function isLocked() : bool
//     {
//         if ( !$this->locked ) {
//             return false;
//         }
//
//         Log::warning(
//                 'The {class} is locked. No further changes can be made at this time.',
//                 [ 'class' => $this::class, 'document' => $this, 'reason' => 'Locked by the RequestResponseHandler.' ],
//         );
//
//         return true;
//     }
//
//     public function set( array | int | string $keys, mixed $value = null ) : Document
//     {
//         if ( $this->isLocked() ) {
//             return $this;
//         }
//         return parent::set( $keys, $value );
//     }
//
//     public function add( array | int | string $keys, mixed $value = null ) : Document
//     {
//         if ( $this->isLocked() ) {
//             return $this;
//         }
//         return parent::add( $keys, $value );
//     }
//
//     public function __invoke(
//             ?string               $title = null,
//             ?string               $description = null,
//             null | string | array $keywords = null,
//             ?string               $author = null,
//             ?string               $id = null,
//             ?string               $status = null,
//     ) : Document
//     {
//         $set = \array_filter( \get_defined_vars() );
//
//         foreach ( $set as $name => $value ) {
//             $this->meta( $name, $value );
//         }
//
//         return $this;
//     }
//
//     /**
//      * Set an arbitrary meta tag.
//      *
//      * - This method does not validate the name or content.
//      * - The name is automatically prefixed with the group if relevant.
//      *
//      * @param string             $name  = ['title', 'description', 'author', 'keywords'][$any]
//      * @param null|array|string  $content
//      *
//      * @return $this
//      */
//     public function meta( string $name, null | string | array $content ) : Document
//     {
//         $this->set( $this->metaGroup( $name ), toString( $content, ', ' ) );
//
//         return $this;
//     }
//
//     public function head( string $key, string | Stringable $html ) : Document
//     {
//         $value = $html instanceof Stringable ? $html->__toString() : $html;
//
//         // TODO : Cache
//         // TODO : Linting / validation step
//
//         $this->set( 'head.' . Normalize::key( $key ), $value );
//
//         return $this;
//     }
//
//     /**
//      * @param string  $bot      = [ 'googlebot', 'bingbot', 'yandexbot'][$any]
//      * @param string  ...$rule  = [
//      *                          'index', 'noindex', 'follow', 'nofollow',
//      *                          'index, follow', 'noindex, nofollow',
//      *                          'noarchive', 'nosnippet', 'nositelinkssearchbox'
//      *                          ][$any]
//      *
//      * @return Document
//      *
//      * @see https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag Documentation
//      */
//     public function robots( string $bot, string ...$rule ) : Document
//     {
//         $rules = [];
//
//         foreach ( $rule as $content ) {
//             if ( !\is_string( $content ) ) {
//                 Log::exception(
//                         exception : new InvalidArgumentException( $this::class ),
//                         message   : 'Invalid robots rule for {bot}, a string is required, but {type} was provided.',
//                         context   : [ 'bot' => $bot, 'type' => \gettype( $content ) ],
//                 );
//
//                 continue;
//             }
//
//             if ( \str_contains( $content, ',' ) ) {
//                 foreach ( \explode( ',', $content ) as $value ) {
//                     $rules[] = \trim( $value );
//                 }
//             }
//             else {
//                 $rules[] = \trim( $content );
//             }
//         }
//
//         $this->set( "robots.{$bot}", \implode( ', ', $rules ) );
//
//         return $this;
//     }
//
//     /**
//      * Enqueue assets using their `name`.
//      *
//      * The {@see ResponseHandler} will ensure the requested assets are provided if needed.
//      *
//      * @param string  ...$enqueue
//      *
//      * @return $this
//      */
//     public function assets( string ...$enqueue ) : Document
//     {
//         foreach ( $enqueue as $asset ) {
//             $this->push( 'assets', $asset );
//         }
//
//         return $this;
//     }
//
//     /**
//      * @param Path|string  $path
//      * @param ?string      $id
//      * @param bool         $inline
//      *
//      * @return $this
//      */
//     public function style(
//             string | Path $path, // 'core.{name}' | path
//             ?string       $id = null,
//             bool          $inline = false,
//     ) : Document
//     {
//         return $this->add(
//                 'style',
//                 [
//                         'path'   => $path,
//                         'id'     => $id,
//                         'inline' => $inline,
//                 ],
//         );
//     }
//
//     /**
//      * @param Path|string  $path
//      * @param ?string      $id
//      * @param bool         $inline
//      *
//      * @return $this
//      */
//     public function script(
//             string | Path $path, // 'core.{name}' | path
//             ?string       $id = null,
//             bool          $inline = false,
//     ) : Document
//     {
//         return $this->add(
//                 'script',
//                 [
//                         'path'   => $path,
//                         'id'     => $id,
//                         'inline' => $inline,
//                 ],
//         );
//     }
//
//     /**
//      * @param string  $href
//      * @param         $attributes
//      *
//      * @return Document
//      *
//      * @see MDN https://developer.mozilla.org/en-US/docs/Web/HTML/Element/link
//      */
//     public function link( string $href, ...$attributes ) : Document
//     {
//         return $this->add( 'link', [ 'href' => $href ] + $attributes );
//     }
//
//     public function theme(
//             string  $color,
//             string  $scheme = 'dark light',
//             ?string $name = 'system',
//     ) : Document
//     {
//         // Needs to generate theme.scheme.color,
//         // this is to allow for different colors based on light/dark
//
//         foreach ( [
//                 'color'  => $color,
//                 'scheme' => $scheme,
//                 'name'   => $name,
//         ] as $metaName => $content ) {
//             $this->meta( "theme.{$metaName}", $content );
//         }
//         return $this;
//     }
//
//     public function body( ...$set ) : Document
//     {
//         foreach ( $set as $name => $value ) {
//             $separator = match ( $name ) {
//                 'class' => ' ',
//                 'style' => ';',
//                 default => null,
//             };
//
//             $value = match ( $name ) {
//                 'class', 'style' => \is_array( $value ) ? $value : \explode( $separator, $value ),
//                 default          => $value,
//             };
//
//             $this->set( 'body.' . Normalize::key( $name ), $value );
//         }
//         return $this;
//     }
//
//     private function metaGroup( string $name ) : string
//     {
//         foreach ( $this::META_GROUPS as $group => $names ) {
//             if ( \in_array( $name, $names ) ) {
//                 return "{$group}.{$name}";
//             }
//         }
//         return $name;
//     }
// }
