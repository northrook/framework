<?php

namespace Core\Service;

use Core\Assets\AssetFactory;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class AssetManager extends \Core\Assets\AssetManager
{
    final public function __construct(
        AssetFactory     $factory,
        ?CacheInterface  $cache = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct( $factory, $cache, $logger );
        dump( $this );
    }
}
