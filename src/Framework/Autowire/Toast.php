<?php

namespace Core\Framework\Autowire;

use Core\Service\ToastService;
use Core\Symfony\DependencyInjection\ActionInterface;
use JetBrains\PhpStorm\ExpectedValues;

final class Toast implements ActionInterface
{
    public const string
        INFO    = 'info',
        NOTICE  = 'notice',
        SUCCESS = 'success',
        WARNING = 'warning',
        ERROR   = 'danger';

    public function __construct(
        private readonly ToastService $toast,
    ) {
    }

    public function getService() : ToastService
    {
        return $this->toast;
    }

    /**
     * @param 'danger'|'info'|'notice'|'success'|'warning' $status
     * @param string                                       $message
     * @param null|array|string                            $description [optional] accepts {@see \HTML\Tag::INLINE}
     * @param ?int                                         $timeout     [auto] time in seconds before the toast is dismissed
     * @param ?string                                      $icon        [auto] based on `$status`
     *
     * @return void
     */
    public function __invoke(
        #[ExpectedValues( [self::INFO, self::NOTICE, self::SUCCESS, self::WARNING, self::ERROR] )] string            $status,
        string            $message,
        null|string|array $description = null,
        ?int              $timeout = null,
        ?string           $icon = null,
    ) : void {
        $this->getService()->addMessage( $status, $message, $description, $timeout, $icon );
    }
}
