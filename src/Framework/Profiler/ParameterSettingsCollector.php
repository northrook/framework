<?php

declare(strict_types=1);

namespace Core\Framework\Profiler;

use Core\Symfony\SettingsInterface;
use Override;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;
use Symfony\Component\HttpFoundation\{Request, Response};
use function Support\toString;

final class ParameterSettingsCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly SettingsInterface     $settings,
    ) {
    }

    #[Override]
    public function collect( Request $request, Response $response, ?Throwable $exception = null ) : void
    {
        foreach ( $this->parameterBag->all() as $key => $value ) {
            $this->data['parameter'][]
                    = [
                        'label' => $key,
                        'value' => $this->value( $value ),
                    ];
        }

        foreach ( $this->settings->all() as $key => $value ) {
            $this->data['setting'][] = [
                'label' => $key,
                'value' => $this->value( $value ),
            ];
        }
    }

    public function getParameterCount() : int
    {
        return \count( $this->data['parameter'] ?? [] );
    }

    public function getSettingCount() : int
    {
        return \count( $this->data['setting'] ?? [] );
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function getParameters() : array
    {
        return $this->data['parameter'] ?? [];
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function getSettings() : array
    {
        return $this->data['setting'] ?? [];
    }

    private function value( mixed $value ) : string
    {
        if ( \is_null( $value ) ) {
            return 'null';
        }

        if ( \is_string( $value ) || \is_int( $value ) || \is_float( $value ) ) {
            return (string) $value;
        }

        $paramter = [];

        if ( \is_iterable( $value ) ) {
            foreach ( $value as $valueItem ) {
                $paramter[] = $this->value( $valueItem );
            }
        }
        else {
            return toString( $value );
        }

        return \implode( ', ', $paramter );
    }
}
