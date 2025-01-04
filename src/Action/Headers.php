<?php

declare(strict_types=1);

namespace Core\Action;

use Core\Symfony\DependencyInjection\Autodiscover;
use Support\Interface\ActionInterface;
use Symfony\Component\HttpFoundation\{HeaderBag, RequestStack, ResponseHeaderBag};

// : Content Type
//  https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
//  https://stackoverflow.com/a/48704300/14986455

// : Robots
//  https://www.madx.digital/glossary/x-robots-tag
//  https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag

// Seems unnecessary when we can autowire the official Request and use $request->headers
#[Autodiscover]
final class Headers implements ActionInterface
{
    protected ?HeaderBag $tempHeaderBag;

    public function __construct( private readonly RequestStack $requestStack )
    {
        dump( __CLASS__.' instantiated' );
    }

    /**
     * Set one or more response headers.
     *
     * - Assigned to the {@see ResponseHeaderBag::class}.
     *
     * @param array<string, null|array<string, string>|bool|string>|string $set
     * @param null|bool|string|string[]                                    $value
     * @param bool                                                         $replace [true]
     *
     * @return Headers
     */
    public function __invoke(
        string|array           $set,
        bool|string|array|null $value = null,
        bool                   $replace = true,
    ) : Headers {
        // Set multiple values
        if ( \is_array( $set ) ) {
            foreach ( $set as $key => $value ) {
                $this->__invoke( $key, $value, $replace );
            }

            return $this;
        }

        if ( \is_bool( $value ) ) {
            $value = $value ? 'true' : 'false';
        }

        $this->requestHeaderBag()->set( $set, $value, $replace );

        return $this;
    }

    /**
     * Adds new headers the current HTTP headers set.
     *
     * @param array<string, null|string|string[]> $headers
     */
    public function add( array $headers ) : void
    {
        $this->requestHeaderBag()->add( $headers );
    }

    /**
     * Returns the first header by name or the default one.
     *
     * @param string  $key
     * @param ?string $default
     *
     * @return null|string
     */
    public function get( string $key, ?string $default = null ) : ?string
    {
        return $this->requestHeaderBag()->get( $key, $default );
    }

    /**
     * Sets a header by name.
     *
     * @param string               $key
     * @param null|string|string[] $values  The value or an array of values
     * @param bool                 $replace Whether to replace the actual value or not (true by default)
     */
    public function set( string $key, string|array|null $values, bool $replace = true ) : void
    {
        $this->requestHeaderBag()->set( $key, $values, $replace );
    }

    /**
     * Returns true if the HTTP header is defined.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has( string $key ) : bool
    {
        return $this->requestHeaderBag()->has( $key );
    }

    /**
     * Returns true if the given HTTP header contains the given value.
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function contains( string $key, string $value ) : bool
    {
        return $this->requestHeaderBag()->contains( $key, $value );
    }

    /**
     * Removes a header.
     *
     * @param string $key
     */
    public function remove( string $key ) : void
    {
        $this->requestHeaderBag()->remove( $key );
    }

    /**
     * Access the {@see HeaderBag}.
     *
     * @return HeaderBag
     */
    private function requestHeaderBag() : HeaderBag
    {
        $currentHeaderBag = $this->requestStack->getCurrentRequest()?->headers;

        if ( ! $currentHeaderBag ) {
            return $this->tempHeaderBag ??= new HeaderBag();
        }

        if ( isset( $this->tempHeaderBag ) ) {
            $currentHeaderBag->add( $this->tempHeaderBag->all() );
            $this->tempHeaderBag = null;
        }

        return $currentHeaderBag;
    }
}
