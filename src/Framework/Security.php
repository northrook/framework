<?php

declare(strict_types=1);

namespace Core\Framework;

use Core\Symfony\DependencyInjection\Autodiscover;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Autodiscover(
    tag      : 'core.service_locator',
    autowire : true,
)]
final readonly class Security
{
    public function __construct(
        public AuthorizationChecker $authorizationChecker,
    ) {}

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     *
     * @param mixed $attribute
     *
     * @param mixed $subject
     *
     * @return bool
     */
    public function isGranted( mixed $attribute, mixed $subject = null ) : bool
    {
        return $this->authorizationChecker->isGranted( $attribute, $subject );
    }

    /**
     * Throws an exception unless the attribute is granted against the current authentication token and optionally
     * supplied subject.
     *
     * @param mixed      $attribute
     * @param null|mixed $subject
     * @param string     $message
     *
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessGranted(
        mixed  $attribute,
        mixed  $subject = null,
        string $message = 'Access Denied.',
    ) : void {
        if ( ! $this->isGranted( $attribute, $subject ) ) {
            $exception = new AccessDeniedException( $message );
            $exception->setAttributes( [$attribute] );
            $exception->setSubject( $subject );

            throw $exception;
        }
    }
}
