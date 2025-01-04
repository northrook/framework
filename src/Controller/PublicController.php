<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Framework\Controller\Attribute\OnDocument;
use Core\Service\AssetManager;
use Core\View\{ComponentFactory, Document};
use Symfony\Component\HttpFoundation\Request;
use Core\Action\{Toast};
use Core\Framework\Controller;
use Core\Framework\Controller\Template;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path : '/',
    name : 'public:',
)]
final class PublicController extends Controller
{
    #[OnDocument]
    public function onDocumentResponse( Document $document ) : void
    {
        $document
            ->title( 'Public Document Title' )
            ->assets( 'style.core', 'script.core', 'script.htmx' );
    }

    #[
        Route( [
            'default' => '/',
            'dynamic' => '/{route}',
        ], 'index', priority : -100 ),
        Template( 'welcome.latte' ) // content template
    ]
    public function index( Document $document, Request $request ) : void
    {
        $document( 'Index Demo Template' );
    }

    #[
        Route( '/tailwind', 'tailwind' ),
        Template( 'demo.latte' ) // content template
    ]
    public function tailwind(
        Document $document,
    ) : string {
        $document( 'Tailwind Demo Template' );
        // $document->script( 'https://cdn.tailwindcss.com', 'tailwindcss' );

        return 'tailwind.latte';
    }

    #[
        Route( '/demo', 'demo' ),
        Template( 'demo.latte' ) // content template
    ]
    public function demo(
        Document         $document,
        Toast            $toast,
        AssetManager     $assetManager,
        ComponentFactory $componentFactory,
    ) : string {
        $assetManager->factory->locator()->scan();
        $document( 'Index Demo Template' );

        $toast(
            'info',
            'Useful information Toast.',
            'It has some details as well. How thoughtful.',
        );

        // foreach ( \range( 0, \rand( 2, 7 ) ) as $key => $value ) {
        //     $status      = (string) $toast::STATUS[ \array_rand( $toast::STATUS ) ];
        //     $description = $key % 2 == 0 ? 'Description' : null;
        //
        //     $timeout = 3600 + ( $key * 1000 );
        //
        //     $toast(
        //             $status,
        //             'Hello there, this is a ' . $status . '. Seed: ' . $key,
        //             $description,
        //             $timeout,
        //     );
        // }

        return 'demo.latte';
    }

    #[Route( 'hello', 'boilerplate' )]
    public function boilerplate() : string
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
