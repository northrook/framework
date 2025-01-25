<?php

declare(strict_types=1);

namespace Core\Framework\Profiler;

use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Profiler\Profiler;

final readonly class ProfilerEventHandler
{
    public function __construct(
        private Profiler $profiler,
    ) {}

    public function __invoke( FinishRequestEvent $event ) : void
    {
        dump( $this );
    }
}
