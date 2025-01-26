<?php

namespace Core\Controller;

use Core\Framework\Controller;
use Core\Symfony\DependencyInjection\Autodiscover;
use Core\View\Document;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[
    Autodiscover,
    Route(
        name     : 'security:',
        priority : 1,
    )]
final class SecurityController extends Controller
{
    #[Route(
        path : '/login',
        name : 'login',
    )]
    public function login( Document $document ) : Response
    {
        return new Response();
    }
}
