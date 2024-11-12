<?php

namespace Core\View;

interface ComponentInterface
{
    public static function componentName() : string;

    public function componentUniqueId() : string;

    public function render() : ?string;
}
