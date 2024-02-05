<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchASSetForm;
use App\Repository\AsSetRepository;
use App\Repository\GetAsDataRepository;
use App\Repository\KnowlinksRepository;
use App\Util\Annotation\Menu;
use App\Util\GetStartEndGraph;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Menu('asset')]
class AssetController extends BaseController
{
    protected array $data = [];

    #[Route(
        path: '/asset',
        name: 'asset',
        methods: ['GET|POST'],
    )]
    public function index(
        Request $request,
        AsSetRepository $asSetRepository,
        GetAsDataRepository $asDataRepository,
        GetStartEndGraph $getStartEndGraph,
    ): Response {
        $this->base_data['content_wrapper']['titre'] = 'History for AS-SET';

        $form = $this->createForm(SearchASSetForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->data['asset']['name'] = \strtoupper((string) $form->get('asset')->getData());
            $this->data['asset']['whois'] = $asSetRepository->getAsset($this->data['asset']['name']);

            if (\array_key_exists('as_num', $this->data['asset']['whois'])) {
                $this->data['asset']['data'] = $asDataRepository::get(200, '', [], $this->data['asset']['whois']['as_num']);

                $this->data['selectedLinks'] = [];
                $this->data['graph_size'] = [
                    'width' => $this->configApplication::getAsStatsConfigGraph()['top_graph_width'],
                    'height' => $this->configApplication::getAsStatsConfigGraph()['top_graph_height'],
                ];

                $this->data = \array_merge($this->data, $getStartEndGraph->get());

                return $this->render('pages/asset/show.html.twig', [
                    'base_data' => $this->base_data,
                    'data' => $this->data,
                    'form' => $form->createView(),
                    'knownlinks' => KnowlinksRepository::get(),
                ]);
            }

            $this->addFlash('info', \sprintf('Unable to find information about asset %s', $this->data['asset']['name']));
        }

        return $this->render('pages/asset/index.html.twig', [
            'base_data' => $this->base_data,
            'data' => $this->data,
            'form' => $form->createView(),
        ]);
    }
}
