<?php

declare(strict_types=1);

namespace Core\Service;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\{FlashBagAwareSessionInterface, SessionInterface};

final readonly class ToastService
{
    public function __construct( private SessionInterface $session )
    {
    }

    /**
     * Retrieve the current {@see getFlashBag} from the active {@see Session}.
     *
     * @return FlashBagInterface
     */
    public function getFlashBag() : FlashBagInterface
    {
        \assert( $this->session instanceof FlashBagAwareSessionInterface );

        return $this->session->getFlashBag();
    }
}
