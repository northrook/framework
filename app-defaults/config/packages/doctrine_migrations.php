<?php

declare(strict_types=1);

namespace Symfony\Config;

return static function( DoctrineMigrationsConfig $migrations ) : void {
    $migrations->migrationsPath(
        'DoctrineMigrations',
        '%kernel.project_dir%/migrations',
    );

    $migrations->enableProfiler( false );
};
