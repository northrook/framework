<?php

declare(strict_types=1);

namespace Core\View;

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Parameters
{
    private ?object $object = null;

    /**
     * @var array<string, mixed>
     */
    private array $parameters = [];

    /**
     * Use an object as the TemplateType parameter.
     *
     * This excludes using array $parameters for the template.
     *
     * @param object $object
     *
     * @return void
     */
    public function use( object $object ) : void
    {
        $this->object = $object;
    }

    public function add( string $key, mixed $value ) : Parameters
    {
        $this->parameters[$key] ??= $value;
        return $this;
    }

    public function set( string $key, mixed $value ) : self
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    public function has( string $key ) : bool
    {
        return \array_key_exists( $key, $this->parameters );
    }

    public function get( string $key ) : mixed
    {
        return $this->parameters[$key] ?? null;
    }

    public function getParameters() : object|array
    {
        // TODO : handle array->object
        return $this->object ?? $this->parameters;
    }
}
