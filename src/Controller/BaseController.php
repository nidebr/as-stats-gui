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
        $this->base_data['top_interval'] = $Config::getAsStatsTopInterval();
        $this->base_data['request'] = $requestStack->getCurrentRequest()->query->all();
    }
}
