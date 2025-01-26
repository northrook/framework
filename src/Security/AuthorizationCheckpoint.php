<?php

namespace Core\Security;

use Symfony\Component\HttpFoundation\{RedirectResponse, Request, Response};
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final readonly class AuthorizationCheckpoint implements AuthenticationEntryPointInterface
{
    public function __construct( private UrlGeneratorInterface $url ) {}

    public function start(
        Request                  $request,
        ?AuthenticationException $authException = null,
    ) : Response {
        dump(
            [__METHOD__ => $this],
            ['Request'       => $request],
            ['AuthException' => $authException],
        );
        return new RedirectResponse( $this->url->generate( 'security:login' ) );
    }
}
