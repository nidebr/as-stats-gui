<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ConfigApplication;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected array $base_data = [];

    public function __construct(
        ConfigApplication $Config,
    ) {
        $this->base_data['release'] = $Config::getRelease();
    }
}
