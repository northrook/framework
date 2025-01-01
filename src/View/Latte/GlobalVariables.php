<?php

namespace Core\View\Latte;

use JetBrains\PhpStorm\Deprecated;
use Support\{PropertyAccessor, Time};
use Symfony\Component\HttpFoundation\{Request, RequestStack};
use Symfony\Component\HttpFoundation\Session\{SessionInterface};
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @property-read bool            isDebug
 * @property-read bool            isProd
 * @property-read bool            isDev
 *
 * @property-read Request         $request
 * @property-read string          $pathInfo
 * @property-read string          $method
 *
 * @property-read string          $route
 * @property-read string          $routeName
 * @property-read string          $routeRoot
 *
 * @property-read ?UserInterface  $user
 * @property-read ?TokenInterface $token
 */
#[Deprecated]
final readonly class GlobalVariables
{
    use PropertyAccessor;

    public function __construct(
        public string                     $environment,
        private bool                      $debug,
        private RequestStack              $requestStack,
        private ?TokenStorageInterface    $tokenStorage,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
        dump( $this::class );
        // TODO : Readonly site settings for timestamp etc
    }

    public function __get( string $property )
    {
        $get = match ( $property ) {
            'isDebug' => $this->debug,
            'isProd'  => \str_starts_with( $this->environment, 'prod' ),
            'isDev'   => \str_starts_with( $this->environment, 'dev' ),

            'request'  => $this->currentRequest(),
            'pathInfo' => $this->currentRequest()->getPathInfo(),
            'method'   => $this->currentRequest()->getMethod(),

            'route'     => $this->route(),
            'routeName' => $this->routeName(),
            'routeRoot' => $this->routeRoot(),

            'user'  => $this->tokenStorage->getToken()?->getUser(),
            'token' => $this->tokenStorage->getToken(),
        };

        return $get;
    }

    /**
     * Returns a formatted time string, based on {@see date()}.
     *
     * @param null|int    $timestamp
     * @param null|string $format
     * @param ?string     $timezone
     *
     * @return Time
     */
    public function time( ?int $timestamp = null, ?string $format = null, ?string $timezone = null ) : Time
    {
        // TODO : Get from settings
        $timestamp ??= Time::FORMAT_SORTABLE;
        $timezone  ??= 'UTC';
        return new Time( $format, $timezone, $timestamp );
    }

    public function csrfToken( string $tokenId ) : string
    {
        return $this->csrfTokenManager->getToken( $tokenId )->getValue();
    }

    private function currentRequest() : ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    private function session() : ?SessionInterface
    {
        return $this->currentRequest()?->hasSession() ? $this->currentRequest()->getSession() : null;
    }

    /**
     * Resolve and cache the current route key.
     *
     * @return string
     */
    private function route() : string
    {
        return $this->currentRequest()->attributes->get( 'route' ) ?? '';
    }

    /**
     * Resolve and cache the current route name.
     *
     * @return string
     */
    private function routeName() : string
    {
        return $this->currentRequest()->get( '_route' ) ?? '';
    }

    /**
     * Resolve and cache the current route root name.
     *
     * @return string
     */
    private function routeRoot() : string
    {
        return \strstr( $this->routeName(), ':', true );
    }
}
