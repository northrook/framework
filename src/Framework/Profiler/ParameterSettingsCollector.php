<?php

declare(strict_types=1);

namespace Core\Framework\Profiler;

use Override;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Throwable;
use Symfony\Component\HttpFoundation\{Request, Response};

final class ParameterSettingsCollector extends AbstractDataCollector
{
    private VarCloner $cloner;

    private HtmlDumper $dumper;

    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        // private readonly Settings     $settings,
    ) {}

    #[Override]
    public function collect( Request $request, Response $response, ?Throwable $exception = null ) : void
    {
        $this->dumper ??= new HtmlDumper();
        $this->cloner ??= new VarCloner();
        $this->cloner->addCasters( ReflectionCaster::UNSET_CLOSURE_FILE_INFO );

        foreach ( $this->parameterBag->all() as $key => $value ) {
            $this->data['parameter'][] = $this->setItem( $key, $value );
        }

        // foreach ( $this->settings->all() as $key => $value ) {
        //     $this->data['setting'][$key] = $this->setItem( $key, $value );
        // }
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return array{label: string, value: null|string}
     */
    protected function setItem( string $key, mixed $value ) : array
    {
        return [
            'label' => $key,
            'value' => $this->dumper->dump( $this->cloner->cloneVar( $value ), true ),
        ];
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
}
