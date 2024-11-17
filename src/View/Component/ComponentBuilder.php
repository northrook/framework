<?php

namespace Core\View\Component;

// / Replacement / Merge of Component and Compiler\ComponentBuilder

use Northrook\HTML\Element;
use Northrook\HTML\Element\Attributes;
use Northrook\Logger\Log;
use Psr\Log\LoggerInterface;
use Throwable;
use function Support\classBasename;
use InvalidArgumentException;

abstract class ComponentBuilder implements ComponentInterface
{
    /** @var ?string Define a name for this component */
    protected const ?string NAME = null;

    private readonly string $html;

    protected readonly Element $element;

    protected readonly Attributes $attributes;

    protected readonly string $name;

    protected readonly string $uniqueId;

    abstract protected function build( mixed ...$arguments ) : string;

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

    /**
     * @param array<array-key, mixed> $arguments
     * @param array<string, object>   $autowire
     * @param ?string                 $uniqueId
     * @param ?LoggerInterface        $logger
     *
     * @return ComponentInterface
     */
    public static function create(
        array            $arguments,
        array            $autowire = [],
        ?string          $uniqueId = null,
        ?LoggerInterface $logger = null,
    ) : ComponentInterface {
        return new static();
    }

    private function setComponentUniqueId( ?string $hash = null ) : void
    {
        if ( \strlen( $hash ) === 16 && \ctype_alnum( $hash ) ) {
            $this->uniqueId ??= \strtolower( $hash );
            return;
        }
        $this->uniqueId ??= \hash( algo : 'xxh3', data : $hash );
    }
}
