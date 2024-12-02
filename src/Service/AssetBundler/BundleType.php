<?php

declare(strict_types=1);

namespace Core\Service\AssetBundler;

enum BundleType : string
{
    case STYLE  = 'css';
    case SCRIPT = 'js';
}
