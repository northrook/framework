<?php

/** @noinspection PhpInternalEntityUsedInspection */

declare(strict_types=1);

include_once dirname( __DIR__ ).'/vendor/autoload.php';

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory as Config;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\{AccessToken, UserProvider};
use Symfony\Bundle\FrameworkBundle\DependencyInjection as FrameworkBundle;
use Symfony\Bundle\SecurityBundle\DependencyInjection as SecurityBundle;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection as DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DependencyInjection as DoctrineMigrations;
use Symfony\Component\Config\Builder\ConfigBuilderGenerator;
use Symfony\Component\Config\Definition\ConfigurationInterface;

$bundleMetaDir = dirname( __DIR__ ).DIRECTORY_SEPARATOR.'.ide';

if ( ! file_exists( $bundleMetaDir ) ) {
    mkdir( $bundleMetaDir, 0777, true );
}

$builder = new ConfigBuilderGenerator( $bundleMetaDir );

foreach ( ConfigurationReference() as $configuration ) {
    $builder->build( $configuration )();
}

/**
 * @return ConfigurationInterface[]
 */
function ConfigurationReference() : array
{
    return array_filter(
        [
            new FrameworkBundle\Configuration( false ),
            new DoctrineBundle\Configuration( true ),
            new DoctrineMigrations\Configuration(),
            SecurityConfiguration(),
        ],
    );
}

function SecurityConfiguration() : ?SecurityBundle\MainConfiguration
{
    if ( ! class_exists( SecurityBundle\MainConfiguration::class ) ) {
        return null;
    }

    $accessTokenArguments = [
        new AccessToken\ServiceTokenHandlerFactory(),
        new AccessToken\OidcUserInfoTokenHandlerFactory(),
        new AccessToken\OidcTokenHandlerFactory(),
        new AccessToken\CasTokenHandlerFactory(),
    ];

    return new SecurityBundle\MainConfiguration(
        [
            new Config\FormLoginFactory(),
            new Config\FormLoginLdapFactory(),
            new Config\JsonLoginFactory(),
            new Config\JsonLoginLdapFactory(),
            new Config\HttpBasicFactory(),
            new Config\HttpBasicLdapFactory(),
            new Config\RememberMeFactory(),
            new Config\X509Factory(),
            new Config\RemoteUserFactory(),
            new Config\CustomAuthenticatorFactory(),
            new Config\LoginThrottlingFactory(),
            new Config\LoginLinkFactory(),
            new Config\AccessTokenFactory( $accessTokenArguments ),
        ],
        [
            new UserProvider\InMemoryFactory(),
            new UserProvider\LdapFactory(),
        ],
    );
}
