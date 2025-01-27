<?php

declare(strict_types=1);

namespace Symfony\Config;

return static function( DoctrineConfig $doctrine ) : void {
    $database = $doctrine
        ->dbal()
        ->defaultConnection( 'core' );

    $core_db = $database->connection( 'core' );

    $core_db
        ->driver( 'pdo_sqlite' )
        ->url( 'sqlite:///%kernel.project_dir%/var/core.db' )
        ->useSavepoints( true );

    $core_db
        ->profiling( true )
        ->profilingCollectBacktrace( true )
        ->profilingCollectSchemaErrors( true );

    /** @var Doctrine\OrmConfig $orm */
    $orm = $doctrine->orm();

    $orm->defaultEntityManager( 'core' )
        ->enableLazyGhostObjects( true )
        ->autoGenerateProxyClasses( true );

    $orm->controllerResolver()->autoMapping( false );

    $entityManager = $doctrine->orm()->entityManager( 'core' );

    $entityManager->autoMapping( true );

    $entityManager->mapping(
        'app',
        [
            'type'   => 'attribute',
            'dir'    => '%kernel.project_dir%/src/Entity',
            'prefix' => 'App\Entity',
            'alias'  => 'App',
        ],
    );
    $entityManager->mapping(
        'core',
        [
            'type'   => 'attribute',
            'dir'    => '%dir.core_src%/Entity',
            'prefix' => 'Core\Entity',
            'alias'  => 'Core',
        ],
    );
    $entityManager->mapping(
        'security',
        [
            'type'   => 'attribute',
            'dir'    => '%dir.core_src%/Security/Entity',
            'prefix' => 'Core\Security\Entity',
            'alias'  => 'Security',
        ],
    );
};
