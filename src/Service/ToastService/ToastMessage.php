<?php

namespace Core\Service\ToastService;

use HTML\Tag;
use InvalidArgumentException;
use Support\Time;
use Throwable;

final class ToastMessage
{
    /** @var array<positive-int, ?string> `[timestamp => ?description]` */
    private array $occurrences = [];

    /** @var ?positive-int in seconds */
    private ?int $timeout;

    public readonly string $status;

    public readonly string $message;

    public readonly ?string $icon;

    /**
     * @param string                                       $id          a 16 character hash
     * @param 'danger'|'info'|'notice'|'success'|'warning' $status
     * @param string                                       $message
     * @param null|array|string                            $description [optional] accepts {@see Tag::INLINE}
     * @param ?int                                         $timeout     [auto] time in seconds before the toast is dismissed
     * @param ?string                                      $icon        [auto] based on `$status`
     */
    public function __construct(
        public readonly string $id,
        string                 $status,
        string                 $message,
        null|string|array      $description = null,
        ?int                   $timeout = null,
        ?string                $icon = null,
    ) {
        $this->setStatus( $status );
        $this->message = $this->escapeHtml( $message );
        $this->bump( $description );
        $this->timeout( $timeout );
        $this->setIcon( $icon );
    }

    /**
     * Indicate that this notification has been seen before.
     *
     * - Adds a timestamp to the {@see ToastMessage::$occurrences} array.
     * - May update the `$description`.
     *
     * @param ?string $description
     *
     * @return $this
     */
    public function bump( ?string $description ) : self
    {
        $this->occurrences[Time::now()->unixTimestamp] = $this->escapeHtml( $description );
        return $this;
    }

    public function timeout( ?int $set = null ) : self
    {
        $this->timeout = $set;

        return $this;
    }

    protected function setStatus( string $status ) : void
    {
        if ( ! \ctype_alpha( $status ) ) {
            $message = $this::class.' invalid status type; may only contain ASCII letters.';
            throw new InvalidArgumentException( $message );
        }

        $this->status = \strtolower( $status );
    }

    protected function setIcon( ?string $icon ) : void
    {
        if ( ! \ctype_alpha( \str_replace( [':', '.', '-'], '', $icon ) ) ) {
            $message
                    = $this::class.' invalid icon key; may only contain ASCII letters and colon, period, or hyphens.';
            throw new InvalidArgumentException( $message );
        }

        $this->icon = $icon ? \strtolower( $icon ) : null;
    }

    public function getTimeout() : ?int
    {
        return 'danger' === $this->status ? null : $this->timeout;
    }

    public function getArguments() : array
    {
        $description = \array_reverse( \array_filter( $this->occurrences ) );

        return [
            'id'          => $this->id,
            'status'      => $this->status,
            'message'     => $this->message,
            'description' => $description,
            'timeout'     => $this->getTimeout(),
            'instances'   => $this->occurrences,
            'timestamp'   => \array_key_first( $this->occurrences ),
            'icon'        => $this->icon,
        ];
    }

    private function escapeHtml( null|string|array $string ) : ?string
    {
        if ( \is_array( $string ) ) {
            try {
                $string = \implode( PHP_EOL, $string );
            }
            catch ( Throwable $exception ) {
                throw new InvalidArgumentException( $exception->getMessage() );
            }
        }

        if ( ! $string ) {
            return null;
        }

        $string = \strip_tags( $string, Tag::INLINE );

        return \trim( $string );
    }
}
