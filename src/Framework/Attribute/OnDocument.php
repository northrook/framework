<?php

declare(strict_types=1);

namespace Core\Framework\Attribute;

use Attribute;

/**
 * Trigger a method callback when the {@see ResponseHandler} resolves a full document response.
 *
 * The method can be injected with services tagged using `core.service_locator`.
 */
#[Attribute( Attribute::TARGET_METHOD )]
final class OnDocument {}
