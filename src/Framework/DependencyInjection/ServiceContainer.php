<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Core\Framework\DependencyInjection\Exception\ServiceContainerException;
use Core\Symfony\DependencyInjection\ServiceContainerInterface;
use Northrook\Logger\Log;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\{Request, RequestStack};
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

/**
 * @implements <ServiceContainerInterface>
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
trait ServiceContainer
{
    protected readonly ServiceLocator $serviceLocator;

    #[Required]
    final public function setServiceLocator( ServiceLocator $serviceLocator ) : void
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @final
     *
     * @param null|'debug'|'dev'|'prod'|'test' $is
     *
     * @return bool|string
     */
    final protected function applicationEnvironment( ?string $is = null ) : string|bool
    {
        $env   = (string) $this->getParameterBag()->get( 'kernel.environment' );
        $debug = (bool) $this->getParameterBag()->get( 'kernel.debug' );

        // Log a warning if debugging is enabled in production.
        if ( $debug && 'prod' === $env ) {
            Log::warning( '{Debug} enabled in production.' );
        }

        // Stand-alone debug check
        if ( 'debug' === $is && $debug ) {
            return true;
        }

        // True if the environment matches asked, or true if we are debugging anywhere but production
        if ( $env === $is || ( $is && 'prod' !== $env && $debug ) ) {
            return true;
        }

        // Return the environment string
        return $env;
    }

    final protected function getParameterBag() : ParameterBagInterface
    {
        return $this->serviceLocator( ParameterBagInterface::class );
    }

    /**
     * @final
     *
     * @template Service
     *
     * @param class-string<Service> $get
     * @param bool                  $nullable
     *
     * @return null|Service
     */
    final protected function serviceLocator( string $get, bool $nullable = false )
    {
        try {
            $service = match ( $get ) {
                Request::class => $this->serviceLocator->get( RequestStack::class )->getCurrentRequest(),
                default        => $this->serviceLocator->get( $get ),
            };

            \assert( $service instanceof $get );
        }
        catch ( Throwable $exception ) {
            $exception = new ServiceContainerException( $get, previous : $exception );

            $service = $nullable ? null : throw $exception;

            if ( $this->applicationEnvironment( 'dev' ) ) {
                Log::exception( $exception );
            }
        }

        return $service;
    }
}
