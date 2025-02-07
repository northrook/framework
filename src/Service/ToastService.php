<?php

declare(strict_types=1);

namespace Core\Service;

use Core\Service\ToastService\ToastMessage;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\{FlashBagAwareSessionInterface};

final readonly class ToastService
{
    public function __construct( private Http\RequestStack $requestStack ) {}

    /**
     * @param 'danger'|'info'|'notice'|'success'|'warning' $status
     * @param string                                       $message
     * @param null|array|string                            $description [optional] accepts {@see Tag::INLINE}
     * @param ?int                                         $timeout     [auto] time in seconds before the toast is dismissed
     * @param ?string                                      $icon        [auto] based on `$status`
     *
     * @return self
     */
    public function addMessage(
        string            $status,
        string            $message,
        null|string|array $description = null,
        ?int              $timeout = null,
        ?string           $icon = null,
    ) : self {
        $id = \hash( 'xxh3', $status.$message );

        $toastMessage = $this->getMessage( $id );

        if ( $toastMessage ) {
            $toastMessage->bump( $description );
        }
        else {
            $toastMessage = new ToastMessage( $id, $status, $message, $description, $timeout, $icon );
        }

        $this->getFlashBag()->set( $id, [$toastMessage] );

        return $this;
    }

    public function getMessage( string $id ) : ?ToastMessage
    {
        $message = $this->getFlashBag()->get( $id )[0] ?? null;

        return $message instanceof ToastMessage ? $message : null;
    }

    /**
     * @param bool $peek
     *
     * @return ToastMessage[]
     */
    public function getAllMessages( bool $peek = false ) : array
    {
        $flashBagMessages = $peek ? $this->getFlashBag()->peekAll() : $this->getFlashBag()->all();

        if ( ! $flashBagMessages ) {
            return [];
        }

        $messages = [];

        foreach ( $flashBagMessages as $keyOrType => $message ) {
            \assert( \is_array( $message ) && \is_string( $keyOrType ), __METHOD__ );

            if ( \strlen( $keyOrType ) === 16 && $message[0] instanceof ToastMessage ) {
                $messages[$keyOrType] = $message[0];
            }
            else {
                foreach ( $message as $title ) {
                    $id            = \hash( 'xxh3', $keyOrType.$title );
                    $messages[$id] = new ToastMessage( $id, $keyOrType, $title );
                }
            }
        }

        return $messages;
    }

    public function hasMessages() : bool
    {
        return ! empty( $this->getFlashBag()->peekAll() );
    }

    public function hasMessage( string $id ) : bool
    {
        return $this->getFlashBag()->has( $id );
    }

    /**
     * Retrieve the current {@see getFlashBag} from the active {@see Session}.
     *
     * @return FlashBagInterface
     */
    public function getFlashBag() : FlashBagInterface
    {
        \assert( $this->requestStack->getSession() instanceof FlashBagAwareSessionInterface );

        return $this->requestStack->getSession()->getFlashBag();
    }
}
