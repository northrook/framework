<?php

namespace Core\Security;

use Core\Symfony\DependencyInjection\Autodiscover;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\{BadgeInterface,
    CsrfTokenBadge,
    RememberMeBadge,
    UserBadge
};
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

#[Autodiscover( autowire : true )]
class LoginAuthenticator extends AbstractAuthenticator
{
    public const string HEADER = 'Authorization';

    protected Request $request;

    protected string $username;

    public function __construct(
        protected readonly UrlGeneratorInterface $url,
        protected readonly UserRepository        $user,
    ) {}

    /**
     * Check if the authenticator should be used for the request.
     *
     * - Returning `null` means authenticate() can be called lazily.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports( Request $request ) : bool
    {
        return $request->headers->has( $this::HEADER );
    }

    private function userBadge( ?string $idenfitifer = null ) : UserBadge
    {
        $idenfitifer ??= $this->request->get( 'input_email' );
        if ( ! \is_string( $idenfitifer ) ) {
            $message = __METHOD__.' is unable to retrieve a username.';
            throw new AuthenticationException( $message );
        }
        return new UserBadge(
            userIdentifier : $idenfitifer,
            userLoader     : fn( $user ) => $this->user->getUser( $user ),
        );
    }

    private function passwordCredentials( ?string $password = null ) : PasswordCredentials
    {
        $password ??= $this->request->get( 'input_password' );
        if ( ! \is_string( $password ) ) {
            $message = __METHOD__.' is unable to retrieve a password.';
            throw new AuthenticationException( $message );
        }
        return new PasswordCredentials( $password );
    }

    /**
     * @return BadgeInterface[]
     */
    private function requestBadges() : array
    {
        $badges = [];

        $csrfToken       = $this->request->get( $this::HEADER );
        $form_csrf_token = $this->request->get( '_csrf_token' );

        if ( \is_string( $csrfToken ) && \is_string( $form_csrf_token ) ) {
            $badges[] = new CsrfTokenBadge(
                $csrfToken,
                $form_csrf_token,
            );
        }

        if ( $this->request->get( 'remember_me' ) ) {
            $badges[] = new RememberMeBadge();
        }

        return $badges;
    }

    public function authenticate( Request $request ) : Passport
    {
        $this->request = $request;
        // $username = $request->parameter( 'input_email' );
        // $password = $request->parameter( 'input_password' );

        $passport = new Passport(
            $this->userBadge(),
            $this->passwordCredentials(),
            $this->requestBadges(),
        );

        dump( $passport );

        return $passport;
    }

    public function onAuthenticationSuccess( Request $request, TokenInterface $token, string $firewallName ) : ?Response
    {
        return null;
    }

    public function onAuthenticationFailure( Request $request, AuthenticationException $exception ) : ?Response
    {
        $request->getSession()->set(
            name  : SecurityRequestAttributes::AUTHENTICATION_ERROR,
            value : $exception,
        );

        $data = [
            'message' => \strtr( $exception->getMessageKey(), $exception->getMessageData() ),
        ];

        return new JsonResponse( $data, Response::HTTP_UNAUTHORIZED );
    }
}
