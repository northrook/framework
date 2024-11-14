<?php

namespace Core\View\Latte\Extension;

use Latte\Extension as LatteExtension;
use Latte\Runtime\{Html, HtmlStringable};
use Override;

final class DebugExtension extends LatteExtension
{
    #[Override]
    public function getFunctions() : array
    {
        return [
            'debug_print' => static function( ...$args ) : HtmlStringable {
                \ob_start();
                echo '<pre>';

                foreach ( $args as $arg ) {
                    \print_r( $arg );
                }
                echo '</pre>';
                return new Html( \ob_get_clean() );
            },
            'debug_dump' => static function( ...$args ) : HtmlStringable {
                \ob_start();

                foreach ( $args as $arg ) {
                    dump( $arg );
                }
                return new Html( \ob_get_clean() );
            },
            'debug_dd' => static fn( ...$args ) => dd( $args ),
        ];
    }
}
