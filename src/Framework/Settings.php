<?php

declare(strict_types=1);

namespace Core\Framework;

use Northrook\ArrayStore;

/**
 * @template TKey of array-key
 * @template TValue of mixed
 *
 * @extends ArrayStore<TKey,TValue>
 */
final class Settings extends ArrayStore
{
    /**
     * @param string $storageDirectory
     */
    public function __construct( string $storageDirectory )
    {
        parent::__construct( $storageDirectory, $this::class );
    }
}
