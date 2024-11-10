<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Throwable;

class ServiceInjectionException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    /**
     * @param string         $property
     * @param class-string   $id
     * @param null|string    $message
     * @param null|Throwable $previous
     */
    public function __construct(
        public readonly string $property,
        public readonly string $id,
        ?string                 $message = null,
        ?Throwable              $previous = null,
    ) {
        $message ??= $this->getMessage();

        parent::__construct( $message, 500, $previous );
    }
}
