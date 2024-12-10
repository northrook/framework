<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Cache\MemoizationCache;
use Northrook\Clerk;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final readonly class StaticServiceInitializer
{
    /**
     * @param Clerk            $clerk
     * @param MemoizationCache $memoizationCache
     */
    public function __construct(
        Clerk            $clerk,
        MemoizationCache $memoizationCache,
    ) {
    }

    public function __invoke( RequestEvent $event ) : void
    {
    }
}
