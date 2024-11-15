<?php

declare(strict_types=1);

namespace Core\UI;

use Core\View\Render\ComponentInterface;
use Interface\Printable;
use InvalidArgumentException;
use Northrook\HTML\Element;
use Northrook\HTML\Element\Attributes;
use Northrook\Logger\Log;
use Psr\Log\LoggerInterface;
use Throwable;
use function Support\classBasename;

abstract class Component implements ComponentInterface
{
    protected const ?string NAME = null;

    private readonly string $html;

    protected readonly Element $element;

    protected readonly Attributes $attributes;

    protected readonly string $name;

    protected readonly string $uniqueId;

    /**
     * @param string                                              $tag
     * @param array                                               $attributes
     * @param array<array-key, Printable|string>|Printable|string $content
     * @param null|string                                         $uniqueId
     * @param ?LoggerInterface                                    $logger
     */
    public function __construct(
        string                              $tag,
        array                               $attributes = [],
        array|string|Printable              $content = [],
        ?string                             $uniqueId = null,
        protected readonly ?LoggerInterface $logger = null,
    ) {
        $subtypes = \explode( ':', $tag );
        $tag      = \array_shift( $subtypes );

        $this->element    = new Element( $tag, $attributes, $content );
        $this->attributes = $this->element->attributes;

        $this->setComponentUniqueId(
            $uniqueId ?? \serialize( [$tag, $attributes, $content, $this->element] ).\spl_object_id( $this ),
        );

        foreach ( $subtypes as $subtype ) {
            if ( ! \method_exists( $this, $subtype ) ) {
                Log::error( $this::class.' component requested unknown subtype '.$subtype );
            }
            else {
                $this->{$subtype}();
            }
        }
    }

    /**
     * Called when the Component is stringified.
     *
     * @return string
     */
    abstract protected function build() : string;

    private function setComponentUniqueId( ?string $hash = null ) : void
    {
        if ( \strlen( $hash ) === 16 && \ctype_alnum( $hash ) ) {
            $this->uniqueId ??= \strtolower( $hash );
            return;
        }
        $this->uniqueId ??= \hash( algo : 'xxh3', data : $hash );
    }

    final public static function componentName() : string
    {
        $name = self::NAME ?? static::class;

        $name = \strtolower( classBasename( $name ) );

        if ( ! $name || ! \preg_match( '/^[a-z0-9:]+$/', $name ) ) {
            $message = 'The name must be lower-case alphanumeric.';

            if ( \is_numeric( $name[0] ) ) {
                $message = 'The name cannot start with a number.';
            }

            if ( \str_starts_with( $name, ':' ) || \str_ends_with( $name, ':' ) ) {
                $message = 'The name must not start or end with a separator.';
            }

            throw new InvalidArgumentException( $name );
        }

        return $name;
    }

    final public function componentUniqueId() : string
    {
        return $this->uniqueId;
    }

    final public function render() : ?string
    {
        try {
            return $this->html ??= $this->build();
        }
        catch ( Throwable $exception ) {
            Log::exception( $exception );
            return null;
        }
    }

    final public function __toString() : string
    {
        return $this->render();
    }
}
