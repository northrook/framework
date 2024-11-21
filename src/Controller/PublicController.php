<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Framework\Attribute\OnDocument;
use Core\Framework\Autowire\Pathfinder;
use Core\Framework\Controller;
use Core\Framework\Controller\Template;
use Core\Framework\Response\{Document, Parameters};
use Latte\Engine;
use Symfony\Component\Routing\Attribute\Route;

#[
    Route( '/', 'public:' ),
]
final class PublicController extends Controller
{
    use Pathfinder;

    #[OnDocument]
    public function onDocumentResponse( Document $document ) : void
    {
        $document->add(
            [
                'html.lang'   => 'en',
                'html.id'     => 'top',
                'html.theme'  => $document->get( 'theme.name' ) ?? 'system',
                'html.status' => 'init',
            ],
        )
            ->add( 'meta.viewport', 'width=device-width,initial-scale=1' );
    }

    #[
        Route( ['/', '/{route}'], 'index', priority : -100 ),
        Template( 'welcome.latte' ) // content template
    ]
    public function index(
        Document   $document,
        Parameters $parameters,
    ) : void {
        $document( 'Index Demo Template' );
        $parameters->set( 'content', 'Hello there!' );
    }

    #[
        Route( ['/demo'], 'demo' ),
        Template( 'demo.latte' ) // content template
    ]
    public function demo(
        Document   $document,
        Parameters $parameters,
    ) : string {
        $document( 'Index Demo Template' );
        $parameters->set( 'content', 'Hello there!' );

        return 'demo.latte';
    }

    #[Route( 'toast', 'notification' )]
    public function notification() : void
    {
        $latte    = new Engine();
        $template = $latte->createTemplate( $this->pathfinder( 'dir.core.templates/component/toast.latte' ) );

        dump( $template );
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
