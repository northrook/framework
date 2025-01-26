<?php

namespace Core\Security;

use Core\Symfony\DependencyInjection\Autodiscover;
use Symfony\Component\HttpFoundation\{RedirectResponse, Request, Response};
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

#[Autodiscover( autowire : true )]
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
