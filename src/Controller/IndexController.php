<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ConfigErrorException;
use App\Exception\DbErrorException;
use App\Exception\KnownLinksEmptyException;
use App\Form\LegendForm;
use App\Repository\GetAsDataRepository;
use App\Repository\KnowlinksRepository;
use App\Util\Annotation\Menu;
use App\Util\GetStartEndGraph;
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
     * @throws KnownLinksEmptyException
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
        GetAsDataRepository $asDataRepository,
        GetStartEndGraph $getStartEndGraph,
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

        $this->data['graph_size'] = [
            'width' => $this->configApplication::getAsStatsConfigGraph()['top_graph_width'],
            'height' => $this->configApplication::getAsStatsConfigGraph()['top_graph_height'],
        ];

        $this->data = \array_merge($this->data, $getStartEndGraph->get());

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
            'data' => $this->data,
            'knownlinks' => KnowlinksRepository::get(),
            'form' => [
                'legend' => $form->createView(),
            ],
        ]);
    }

    /**
     * @throws ConfigErrorException
     * @throws KnownLinksEmptyException
     * @throws DbErrorException
     * @throws Exception
     */
    #[Route(
        path: '/{topinterval}',
        name: 'index.topinterval',
        methods: ['GET|POST'],
    )]
    public function indexTopInterval(
        Request $request,
        GetAsDataRepository $asDataRepository,
        GetStartEndGraph $getStartEndGraph,
        string $topinterval,
    ): Response {
        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'Top %s (%s)',
            $this->configApplication::getAsStatsConfigTop(),
            $this->configApplication::getAsStatsConfigTopInterval()[$topinterval]['label']
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

        $this->data['graph_size'] = [
            'width' => $this->configApplication::getAsStatsConfigGraph()['top_graph_width'],
            'height' => $this->configApplication::getAsStatsConfigGraph()['top_graph_height'],
        ];

        $this->data = \array_merge($this->data, $getStartEndGraph->get($topinterval));

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
            'data' => $this->data,
            'hours' => $this->configApplication::getAsStatsConfigTopInterval()[$topinterval]['label'],
            'knownlinks' => KnowlinksRepository::get(),
            'form' => [
                'legend' => $form->createView(),
            ],
        ]);
    }
}
