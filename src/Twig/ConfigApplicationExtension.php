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
        ];
    }

    /**
     * @throws ConfigErrorException
     */
    public function getGraphShowV6(): bool
    {
        return $this->configApplication::getAsStatsConfigGraph()['showv6'];
    }
}
