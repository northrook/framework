<?php

declare(strict_types=1);

namespace Core\Framework\Controller;

use Attribute;

/**
 * Set the template name to be used by the {@see ResponseHandler}.
 *
 * - When set on an extending {@see Controller}, it will be used as the wrapping layout.
 * - When set on the called `method`, it will provide the content block. or as a stand-alone render for `htmx`.
 */
#[Attribute( Attribute::TARGET_CLASS | Attribute::TARGET_METHOD )]
final class Template
{
    public const string
        DOCUMENT = '_document_template',
        CONTENT  = '_content_template';

    public function __construct( public string $name ) {}
}
