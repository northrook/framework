<?php

declare(strict_types=1);

namespace Core\Framework\DependencyInjection;

use Cache\MemoizationCache;
use Core\Symfony\DependencyInjection\Autodiscover;
use Northrook\Clerk;
use Symfony\Component\HttpKernel\Event\RequestEvent;

// #[Autodiscover(
//     tags     : [
//         'kernel.event_listener' => [
//             'event'    => 'kernel.request',
//             'priority' => 1_024,
//         ],
//     ],
//     autowire : true,
// )]
final readonly class StaticServiceInitializer
{
    /**
     * @param Clerk            $clerk
     * @param MemoizationCache $memoizationCache
     */
    public function __construct( Clerk $clerk, MemoizationCache $memoizationCache ) {}

    public function __invoke( RequestEvent $event ) : void {}
}
