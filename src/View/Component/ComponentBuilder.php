<?php

namespace Core\View\Component;

// / Replacement / Merge of Component and Compiler\ComponentBuilder

use Northrook\HTML\Element;
use Northrook\HTML\Element\Attributes;
use Northrook\Logger\Log;
use Throwable;
use function Support\classBasename;
use InvalidArgumentException;

/**
 */
abstract class ComponentBuilder implements ComponentInterface
{
    /** @var ?string Define a name for this component */
    protected const ?string NAME = null;

    /** @var ?string The default tag for this component */
    protected const ?string TAG = null;

    private readonly string $html;

    protected readonly Element $element;

    protected readonly Attributes $attributes;

    public readonly string $name;

    public readonly string $uniqueId;

    final public function build(
        array   $arguments,
        ?string $uniqueId = null,
    ) : ComponentInterface {
        $this->parseArguments( $arguments );

        $this->name = $this::componentName();

        $this->element = new Element(
            tag        : $this::TAG     ?? $arguments['tag'] ?? 'div',
            attributes : ['attributes'] ?? [],
            content    : ['content']    ?? null,
        );

        $this->attributes = $this->element->attributes;

        $this->setComponentUniqueId(
            $uniqueId ?? \serialize( [$arguments, $this->element] ).\spl_object_id( $this ),
        );

        unset( $arguments['attributes'], $arguments['content'] );

        foreach ( $arguments as $property => $value ) {
            if ( \property_exists( $this, $property ) && ! isset( $this->{$property} ) ) {
                $this->{$property} = $value;

                continue;
            }

            if ( \method_exists( $this, $value ) ) {
                $this->{$value}();
            }

            Log::error(
                'The {component} was provided with undefined property {property}.',
                ['component' => $this->name, 'property' => $property],
            );
        }

        return $this;
    }

    protected function parseArguments( array &$arguments ) : void
    {
    }

    // :: Compile and return the HTML

    /**
     * @return string
     */
    abstract protected function compile() : string;

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
            return $this->html ??= $this->compile();
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

    private function setComponentUniqueId( ?string $hash = null ) : void
    {
        if ( \strlen( $hash ) === 16 && \ctype_alnum( $hash ) ) {
            $this->uniqueId ??= \strtolower( $hash );
            return;
        }
        $this->uniqueId ??= \hash( algo : 'xxh3', data : $hash );
    }
}
