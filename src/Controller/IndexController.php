<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use App\Exception\DbErrorException;
use App\Repository\AsInfoRepository;
use App\Repository\DbAsStatsRepository;
use App\Util\Annotation\Menu;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Menu('top_as')]
class IndexController extends BaseController
{
    protected array $data = [];

    /**
     * @throws ConfigErrorException
     * @throws DbErrorException
     * @throws Exception
     */
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

        $data = new DbAsStatsRepository(ConfigApplication::getAsStatsConfigDayStatsFile());

        foreach ($data->getASStatsTop($this->base_data['top'], []) as $as => $nbytes) {
            $this->data['asinfo'][$as]['v4'] = [
                'in' => $nbytes[0],
                'out' => $nbytes[1],
            ];
        }

        dump($this->data);

        //$dede = AsInfoRepository::get(15169);
        //dump($dede);

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
