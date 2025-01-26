<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Framework\Controller;
use Core\Framework\Controller\Template;
use Symfony\Component\Routing\Attribute\Route;

#[Route( '/admin', 'admin:' ),
    // Template( 'welcome.latte' ) // wrapping body - like Admin UI
]
final class AdminController extends Controller
{
    #[Route(
        [
            'default' => '/',
            'dynamic' => '/{route}',
        ],
        'index',
    )]
    public function index() : string
    {
        return <<<'HTML'
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Sample Page</title>
            </head>
            <body>
                <h1>Hello there!</h1>
                <p>This is a simple HTML boilerplate with a heading and some content. Feel free to customize it as needed.</p>
                <p>HTML is a powerful language for structuring content on the web, and this basic template is a great starting point for building more complex pages.</p>
            </body>
            </html>
            HTML;
    }
}
