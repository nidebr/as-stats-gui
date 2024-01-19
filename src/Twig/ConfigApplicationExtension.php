<?php

declare(strict_types=1);

namespace App\Twig;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ConfigApplicationExtension extends AbstractExtension
{
    public function __construct(
        private ConfigApplication $configApplication,
    ) {
    }

    /**
     * @return array<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('configapplication_graph_showv6', [$this, 'getGraphShowV6']),
            new TwigFunction('configapplication_graph_outispositive', [$this, 'getGraphOutIsPositive']),
            new TwigFunction('configapplication_graph', [$this, 'getConfigGraph']),
        ];
    }

    /**
     * @throws ConfigErrorException
     */
    public function getGraphShowV6(): bool
    {
        return $this->configApplication::getAsStatsConfigGraph()['showv6'];
    }

    public function getGraphOutIsPositive(): bool
    {
        return $this->configApplication::getAsStatsConfigGraph()['outispositive'];
    }

    public function getConfigGraph(string $key): mixed
    {
        if ($key === '' || $key === '0') {
            return '';
        }

        if (false === \array_key_exists($key, $this->configApplication::getAsStatsConfigGraph())) {
            throw new ConfigErrorException(\sprintf('Unable to find config.graph.%s variable', $key));
        }

        return $this->configApplication::getAsStatsConfigGraph()[$key];
    }
}
