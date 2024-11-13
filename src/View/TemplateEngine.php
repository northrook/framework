<?php

namespace Core\View;

use Core\View\Latte\TemplateChainLoader;
use Latte\{Engine, Loader, Loaders\FileLoader};
use Northrook\Filesystem\File;
use Northrook\Logger\Log;

final class TemplateEngine
{
    private readonly Engine $engine;

    private readonly TemplateChainLoader $templateLoader;

    /**
     * @param string                   $projectDirectory
     * @param string[]                 $templateDirectories
     * @param string                   $cacheDirectory
     * @param string                   $locale
     * @param bool                     $autoRefresh
     * @param \Latte\Extension[]       $extensions
     * @param array<array-key, object> $variables
     */
    public function __construct(
        protected string       $projectDirectory,
        string|array           $templateDirectories,
        protected string       $cacheDirectory,
        protected string       $locale = 'en',
        public bool            $autoRefresh = true,
        private readonly array $extensions = [],
        private readonly array $variables = [],
    ) {
        $this->templateLoader = new TemplateChainLoader( $this->projectDirectory );

        foreach ( (array) $templateDirectories as $index => $directory ) {
            $priority = ( $index + 1 ). 0;
            $this->templateLoader->add( $directory, $priority );
        }
    }

    final public function render(
        string       $template,
        object|array $parameters = [],
        ?string      $block = null,
    ) : string {
        $content = $this->engine()->renderToString(
            $this->templateLoader->load( $template ),
            $this->global( $parameters ),
            $block,
        );

        return $content;
    }

    final public function clearTemplateCache() : bool
    {
        return File::remove( $this->cacheDirectory );
    }

    final public function pruneTemplateCache() : void
    {
        $templates = [];

        foreach ( \glob( $this->cacheDirectory.'/*.php' ) as $file ) {
            $templates[\basename( $file )] = $file;
        }

        Log::info(
            'Pruned {count} templates from cache.',
            [
                'count'  => \count( $templates ),
                'pruned' => $templates,
            ],
        );
    }

    final public function engine( ?Loader $loader = null ) : Engine
    {
        $this->engine ?? $this->startEngine( $loader );

        if ( $loader ) {
            $this->engine->setLoader( $loader );
        }

        return $this->engine;
    }

    private function startEngine( ?Loader $loader ) : Engine
    {
        if ( ! \file_exists( $this->cacheDirectory ) ) {
            File::mkdir( $this->cacheDirectory );
        }

        // Initialize the Engine.
        $this->engine = new Engine();

        // Add all registered extensions to the Engine.
        \array_map( [$this->engine, 'addExtension'], $this->extensions );

        $this->engine
            ->setTempDirectory( $this->cacheDirectory )
            ->setAutoRefresh( $this->autoRefresh )
            ->setLoader( $loader ?? new FileLoader() )
            ->setLocale( $this->locale );

        Log::info(
            'Started Latte Engine {id}.',
            [
                'id'     => \spl_object_id( $this->engine ),
                'engine' => $this->engine,
            ],
        );

        return $this->engine;
    }

    /**
     * Adds {@see Latte::$globalVariables} to all templates.
     *
     * - {@see $globalVariables} are not available when using Latte `templateType` objects.
     *
     * @param array<array-key,mixed>|object $parameters
     *
     * @return array<array-key,mixed>|object
     */
    private function global( object|array $parameters ) : object|array
    {
        if ( \is_object( $parameters ) ) {
            return $parameters;
        }

        return $this->variables + $parameters;
    }
}
