<?php

namespace Core\Framework\DependencyInjection;

use Cache\MemoizationCache;

final class StaticServiceInitializer
{
    public function __construct( MemoizationCache $memoizationCache )
    {
    }
}
