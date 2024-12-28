<?php

declare(strict_types=1);

namespace Core\View\Component;

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

use Core\Service\IconService;
use Core\View\Attribute\ViewComponent;
use Support\Time;

#[ViewComponent( 'toast:{status}' )]
final class Toast extends AbstractComponent
{
    public string $status = 'notice';

    public string $message;

    public ?string $description = null;

    public ?int $timeout = null;

    public int $timestamp;

    public string $when;

    public string $icon;

    public function __construct( private readonly IconService $iconService ) {}

    protected function prepareArguments( array &$arguments ) : void
    {
        $timestamp = new Time( $arguments['instances'][0] ?? $arguments['timestamp'] ?? 'now' );

        $this->timestamp = $timestamp->unixTimestamp;
        $this->when      = $timestamp->format( $timestamp::FORMAT_HUMAN, true );

        $this->icon = $arguments['icon'] ?? $arguments['status'] ?? 'notice';
    }

    private function details() : string
    {
        if ( $this->description ) {
            return <<<HTML
                <details>
                    <summary>Description</summary>
                    {$this->description}
                </details>
                HTML;
        }
        return '';
    }

    private function icon(
        string  $height = '1rem',
        ?string $width = null,
    ) : string {
        $width ??= $height;
        return $this->iconService->getIcon(
            $this->icon,
            ['height' => $height, 'width' => $width],
        ) ?? '';
    }

    protected function render() : string
    {
        return <<<HTML
            <toast {$this->attributes}>
                <button class="close" aria-label="Close" type="button"></button>
                <output role="status">
                    <i class="status">
                        {$this->icon()}
                        <span class="status-type">{$this->status}</span>
                        <time datetime="{$this->timestamp}">
                            {$this->when}
                        </time>
                    </i>
                    <span class="message">{$this->message}</span>
                </output>
                {$this->details()}
            </toast>
            HTML;
    }
}
