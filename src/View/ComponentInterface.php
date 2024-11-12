<?php

namespace Core\View;

interface ComponentInterface
{
    public function componentName() : string;

    public function componentUniqueId() : string;

    public function render() : ?string;
}
