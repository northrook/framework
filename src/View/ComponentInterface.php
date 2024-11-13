<?php

namespace Core\View;

// The __constructor sort has to be a set standard
// We could have an abstract static for 'default' initialization?

interface ComponentInterface
{

    public static function componentName() : string;

    public function componentUniqueId() : string;

    public function render() : ?string;
}
