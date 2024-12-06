<?php

namespace Core\Framework\Autowire;

use Core\Service\ToastService;
use Core\Symfony\DependencyInjection\ActionInterface;

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
     * @param string            $status
     * @param string            $message
     * @param null|array|string $description
     * @param ?int              $timeout
     *
     * @return void
     */
    public function __invoke(
        string            $status,
        string            $message,
        null|string|array $description = null,
        ?int              $timeout = null,
    ) : void {
        // TODO: Implement __invoke() method.
    }
}
