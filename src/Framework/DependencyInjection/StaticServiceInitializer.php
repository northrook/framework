<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Cache\MemoizationCache;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class StaticServiceInitializer
{
    public function __construct( MemoizationCache $memoizationCache )
    {
    }

    public function __invoke( RequestEvent $event ) : void
    {
    }
}
