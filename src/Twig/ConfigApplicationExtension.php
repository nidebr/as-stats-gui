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
            new TwigFunction('configapplication_graph', [$this, 'getConfigGraph']),
            new TwigFunction('configapplication_myasn', [$this, 'getConfigMyAsn']),
        ];
    }

    public function getConfigGraph(string $key): mixed
    {
        if ('' === $key || '0' === $key) {
            return '';
        }

        if (false === \array_key_exists($key, $this->configApplication::getAsStatsConfigGraph())) {
            throw new ConfigErrorException(\sprintf('Unable to find config.graph.%s variable', $key));
        }

        return $this->configApplication::getAsStatsConfigGraph()[$key];
    }

    public function getConfigMyAsn(): ?int
    {
        return $this->configApplication::getAsStatsConfigMyAsn();
    }
}
