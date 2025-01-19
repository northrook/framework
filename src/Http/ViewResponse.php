<?php

namespace Core\Http;

use Core\Http\ViewResponse\LoginFormView;
use Symfony\Component\HttpFoundation\Response;

class ViewResponse extends Response
{
    public static function loginForm( string $name ) : LoginFormView
    {
        return new LoginFormView();
    }
}
