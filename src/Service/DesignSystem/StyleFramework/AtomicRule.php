<?php

declare(strict_types=1);

namespace Core\Service\DesignSystem\StyleFramework;

use function String\escape;

function escapeProperty( string $property ) : string
{
    return \trim( escape( $property, ':' ) );
}

abstract class AtomicRule
{
    protected array $variables = [];

    protected array $rules = [];

    final private function __construct()
    {
    }

    abstract protected function variables() : array;

    abstract protected function rules() : array;

    final public static function generate() : array
    {
        return ( new static() )();
    }

    final public function __invoke() : array
    {
        foreach ( $this->variables() as $variable => $value ) {
            $this->variables[$this->selectorVariable( $variable )] = $value;
        }

        $this->rules[':root'] = $this->variables;

        foreach ( $this->rules() as $selector => $declarations ) {
            $selector = $this->selectorClass( $selector );

            if ( isset( $this->rules[$selector] ) ) {
                $this->rules[$selector] = \array_merge( $this->rules[$selector], $declarations );
            }
            else {
                $this->rules[$selector] = $declarations;
            }
        }

        return $this->rules;
    }

    final protected function selectorVariable( string $variable ) : string
    {
        return '--'.\trim( escape( $variable, ':' ), " \n\r\t\v\0:-" );
    }

    final protected function selectorClass( string $selector ) : string
    {
        return '.'.\trim( escape( $selector, ':' ), '.' );
    }
}
