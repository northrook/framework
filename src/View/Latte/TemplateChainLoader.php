<?php

declare(strict_types=1);

namespace Core\View\Latte;

use Northrook\Logger\Log;
use Support\Normalize;

/**
 * @internal
 */
final class TemplateChainLoader
{
    private bool $locked = false;

    /** @var array<array-key, string> */
    private array $templateDirectories = [];

    public function __construct( private readonly string $projectDirectory ) {}

    public function add( string $path, ?int $priority = null ) : void
    {
        if ( $this->locked ) {
            Log::warning(
                'Template directory cannot be added, the Loader is locked. The Loader is locked automatically when any template is first read.',
            );
            return;
        }

        // TODO : Handle priority collision
        $priority ??= \count( $this->templateDirectories );

        $path = Normalize::path( $path );

        $isset = \array_search( $path, $this->templateDirectories );

        if ( $isset ) {
            unset( $this->templateDirectories[$isset] );
        }

        $this->templateDirectories[$priority] = $path;
    }

    /**
     * @param string $template
     *
     * @return string
     */
    public function load( string $template ) : string
    {
        if ( ! $this->locked ) {
            \krsort( $this->templateDirectories, SORT_DESC );
            $this->locked = true;
        }

        if ( ! \str_ends_with( $template, '.latte' ) ) {
            return $template;
        }

        $template = Normalize::path( $template );

        if ( \str_starts_with( $template, $this->projectDirectory ) && \file_exists( $template ) ) {
            return $template;
        }

        foreach ( $this->templateDirectories as $directory ) {
            if ( \str_starts_with( $template, $directory ) && \file_exists( $directory ) ) {
                return $template;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$template;

            if ( \file_exists( $path ) ) {
                return $path;
            }
        }

        return $template;
    }
}
