<?php

namespace Core\Security;

use Core\Symfony\DependencyInjection\Autodiscover;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

#[Autodiscover( autowire : true )]
final class AccessDenied implements AccessDeniedHandlerInterface
{
    public const string MESSAGE = 'Access Denied';

    /** @var int `403 Forbidden` */
    public const int    CODE = 403;

    /**
     * @param Request               $request
     * @param AccessDeniedException $accessDeniedException
     *
     * @return Response
     */
    public function handle(
        Request               $request,
        AccessDeniedException $accessDeniedException,
    ) : Response {
        // ? Intercept and redirect to an error message
        // ? or redirect to a login page if protected content
        // ? if is an ajax request, return a flash message

        // : else we return a 403 error

        return new Response(
            AccessDenied::MESSAGE,
            AccessDenied::CODE,
        );
    }
}
