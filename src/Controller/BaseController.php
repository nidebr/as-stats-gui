<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ConfigApplication;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

class BaseController extends AbstractController
{
    protected array $base_data = [];

    public function __construct(
        ConfigApplication $Config,
        RequestStack $requestStack,
    ) {
        $this->base_data['release'] = $Config::getRelease();
        $this->base_data['top_interval'] = $Config::getAsStatsConfigTopInterval();
        $this->base_data['request'] = $requestStack->getCurrentRequest()->query->all(); /* @phpstan-ignore-line */

        $this->base_data['top'] = $Config::getAsStatsConfigTop();

        if (\array_key_exists('top', $this->base_data['request'])) {
            $this->base_data['request']['top'] = (int) $this->base_data['request']['top'];

            if ($this->base_data['request']['top'] > 200) {
                $this->base_data['top'] = $this->base_data['request']['top'] = 200;
            } elseif (0 !== $this->base_data['request']['top']) {
                $this->base_data['top'] = $this->base_data['request']['top'];
            }
        }
    }
}
