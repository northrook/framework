<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Framework\Controller;
use Symfony\Component\Routing\Attribute\Route;

final class FaviconController extends Controller
{
    #[Route( '/favicon.ico', 'core:favicon' )]
    public function index() : mixed
    {
        throw $this->notFoundException();
    }
}
