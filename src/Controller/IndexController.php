<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use App\Exception\DbErrorException;
use App\Form\LegendForm;
use App\Repository\GetAsDataRepository;
use App\Repository\KnowlinksRepository;
use App\Util\Annotation\Menu;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Request;
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
        methods: ['GET|POST'],
    )]
    public function index(
        Request $request,
        ConfigApplication $Config,
        GetAsDataRepository $asDataRepository,
    ): Response {
        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'Top %s (%s)',
            $this->base_data['top'],
            '24 hours'
        );

        $form = $this->createForm(LegendForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->data['data'] = $asDataRepository::get($this->base_data['top'], '', (array) $form->getData());
            $this->data['selectedLinks'] = KnowlinksRepository::select((array) $form->getData());
        } else {
            $this->data['data'] = $asDataRepository::get($this->base_data['top']);
            $this->data['selectedLinks'] = [];
        }

        $this->data['start'] = time() - 24 * 3600;
        $this->data['end'] = time();
        $this->data['graph_size'] = [
            'width' => $Config::getAsStatsConfigGraph()['top_graph_width'],
            'height' => $Config::getAsStatsConfigGraph()['top_graph_height'],
        ];

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
            'data' => $this->data,
            'knownlinks' => KnowlinksRepository::get(),
            'form' => [
                'legend' => $form->createView(),
            ],
        ]);
    }

    #[Route(
        path: '/{topinterval}',
        name: 'index_topinterval',
        methods: ['GET|POST'],
    )]
    public function indexTopInterval(
        Request $request,
        ConfigApplication $Config,
        GetAsDataRepository $asDataRepository,
        string $topinterval,
    ): Response {
        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'Top %s (%s)',
            $Config::getAsStatsConfigTop(),
            $Config::getAsStatsConfigTopInterval()[$topinterval]['label']
        );

        $form = $this->createForm(LegendForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->data['data'] = $asDataRepository::get($this->base_data['top'], $topinterval, (array) $form->getData());
            $this->data['selectedLinks'] = KnowlinksRepository::select((array) $form->getData());
        } else {
            $this->data['data'] = $asDataRepository::get($this->base_data['top'], $topinterval);
            $this->data['selectedLinks'] = [];
        }

        $this->data['start'] = time() - $Config::getAsStatsConfigTopInterval()[$topinterval]['hours'] * 3600;
        $this->data['end'] = time();
        $this->data['graph_size'] = [
            'width' => $Config::getAsStatsConfigGraph()['top_graph_width'],
            'height' => $Config::getAsStatsConfigGraph()['top_graph_height'],
        ];

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
            'data' => $this->data,
            'hours' => $Config::getAsStatsConfigTopInterval()[$topinterval]['label'],
            'knownlinks' => KnowlinksRepository::get(),
            'form' => [
                'legend' => $form->createView(),
            ],
        ]);
    }
}
