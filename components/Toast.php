<?php

namespace Core\View\Component;

use Core\View\Attribute\ViewComponent;
use Core\View\Component;
use Core\View\Template\TemplateCompiler;
use Latte\Runtime\Html;
use Support\Time;

/*
 # Accessibility
 : https://github.com/WICG/accessible-notifications
 : https://inclusive-components.design/notifications/

    - Don't use aria-atomic="true" on live elements, as it will announce any change within it.
    - Be judicious in your use of visually hidden live regions. Most content should be seen and heard.
    - Distinguish parts of your interface in content or with content and style, but never just with style.
    - Do not announce everything that changes on the page.
    - Be very wary of Desktop notifications, may cause double announcements etc.


 : https://atlassian.design/components/flag/examples
    Used for confirmations, alerts, and acknowledgments
    that require minimal user interaction.

 : https://atlassian.design/components/banner/examples
    Banner displays a prominent message at the top of the screen.

    We may want to create a separate component, or have types
    such as 'floating' using the Toast system, or 'static'
    using fixed positioning 'top|bottom' with left/right/center.

 */

#[ViewComponent( 'toast:{status}' )]
final class Toast
{
    // public string $status = 'notice';
    //
    // public string $message;
    //
    // public ?string $description = null;
    //
    // public ?int $timeout = null;
    //
    // public int $timestamp;
    //
    // public Html $when;
    //
    // public string $icon;
    //
    // protected function parseArguments( array &$arguments ) : void
    // {
    //     $timestamp = new Time( $arguments['instances'][0] ?? $arguments['timestamp'] ?? 'now' );
    //
    //     $this->timestamp = $timestamp->unixTimestamp;
    //     $this->when      = new Html( $timestamp->format( $timestamp::FORMAT_HUMAN, true ) );
    //
    //     $this->icon = $arguments['icon'] ?? $arguments['status'] ?? 'notice';
    // }
    //
    // protected function compile( TemplateCompiler $compiler ) : string
    // {
    //     return $compiler->render( __DIR__.'/toast.latte', $this );
    // }
}
