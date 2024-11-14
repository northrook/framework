<?php

declare(strict_types=1);

namespace Core\View\Latte\Extension;

use Core\View\Latte\CacheRuntime;
use Core\View\Latte\Node\CacheNode;
use Latte;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Integrates {@see CacheInterface} into the {@see Latte\Engine} using a {@see Latte\Compiler\Tag}.
 */
final class CacheExtension extends Latte\Extension
{
    public function __construct(
        private readonly ?CacheInterface  $cacheInterface,
        private readonly ?LoggerInterface $logger = null,
        private readonly string           $tagName = 'cache',
    ) {
        $this->logger->notice( 'Cache extension started' );
    }

    public function getTags() : array
    {
        return [$this->tagName => [CacheNode::class, 'create']];
    }

    /**
     * Add to the {@see CacheRuntime} to the `$this->global` Latte variable.
     */
    public function getProviders() : array
    {
        return ['cacheRuntime' => new CacheRuntime( $this->cacheInterface, $this->logger )];
    }
}
