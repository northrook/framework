<?php

/** @noinspection ALL */

declare(strict_types=1);

require_once dirname( __DIR__ ).'/vendor/autoload_runtime.php';

return static fn( array $context ) => new \App\Kernel(
    (string) $context['APP_ENV'],
    (bool) $context['APP_DEBUG'],
);
