<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ConfigApplication;
use App\Repository\Db;
use App\Util\Annotation\Menu;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Menu('top_as')]
class IndexController extends BaseController
{
    #[Route(
        path: '/',
        name: 'index',
        methods: ['GET'],
    )]
    public function index(): Response
    {
        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'Top %s (%s)',
            $this->base_data['top'],
            '24 hours'
        );

        $data = new Db(ConfigApplication::getAsStatsConfigDayStatsFile());
        dump($data->getASStatsTop(20, []));

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
        ]);
    }

    #[Route(
        path: '/{topinterval}',
        name: 'index_topinterval',
        methods: ['GET'],
    )]
    public function indexTopInterval(
        ConfigApplication $Config,
        string $topinterval,
    ): Response {
        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'Top %s (%s)',
            $Config::getAsStatsConfigTop(),
            $Config::getAsStatsConfigTopInterval()[$topinterval]['label']
        );

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
        ]);
    }
}
