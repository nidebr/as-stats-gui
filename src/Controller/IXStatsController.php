<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ConfigErrorException;
use App\Exception\DbErrorException;
use App\Exception\KnownLinksEmptyException;
use App\Form\SearchIXForm;
use App\Form\SelectMyIXForm;
use App\Repository\GetAsDataRepository;
use App\Repository\KnowlinksRepository;
use App\Repository\PeeringDBRepository;
use App\Util\Annotation\Menu;
use App\Util\GetJsonParameters;
use App\Util\GetStartEndGraph;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    path: '/ix',
)]
#[Menu('view_ix')]
class IXStatsController extends BaseController
{
    protected array $data = [];

    /**
     * @throws ConfigErrorException
     * @throws DbErrorException
     * @throws Exception
     * @throws KnownLinksEmptyException
     */
    #[Route(
        path: '/my-ix',
        name: 'ix.my_ix',
        methods: ['GET|POST'],
    )]
    public function myIX(
        Request $request,
        PeeringDBRepository $peeringDBRepository,
        GetAsDataRepository $asDataRepository,
        GetStartEndGraph $getStartEndGraph,
    ): Response {
        $this->base_data['content_wrapper']['titre'] = 'My IX Stats';

        $form = $this->createForm(SelectMyIXForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ixInfo = $peeringDBRepository->getIXInfo((int) $form->get('myix')->getData());

            $this->base_data['content_wrapper']['titre'] = \sprintf(
                'Top %s (%s)',
                $this->base_data['top'],
                '24 hours'
            );

            $this->base_data['content_wrapper']['small'] = $ixInfo['name'];
            $this->data['data'] = $asDataRepository::get(
                $this->base_data['top'],
                '',
                [],
                $peeringDBRepository->getIXMembers((int) $form->get('myix')->getData()),
            );

            $this->data['graph_size'] = [
                'width' => $this->configApplication::getAsStatsConfigGraph()['top_graph_width'],
                'height' => $this->configApplication::getAsStatsConfigGraph()['top_graph_height'],
            ];
            $this->data['selectedLinks'] = [];

            $this->data = \array_merge($this->data, $getStartEndGraph->get());

            return $this->render('pages/ix/my_ix/show.html.twig', [
                'base_data' => $this->base_data,
                'data' => $this->data,
                'knownlinks' => KnowlinksRepository::get(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('pages/ix/my_ix/index.html.twig', [
            'base_data' => $this->base_data,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws ConfigErrorException
     * @throws KnownLinksEmptyException
     * @throws DbErrorException
     * @throws Exception
     */
    #[Route(
        path: '/search',
        name: 'ix.search',
        methods: ['GET|POST'],
    )]
    public function searchIX(
        Request $request,
        PeeringDBRepository $peeringDBRepository,
        GetAsDataRepository $asDataRepository,
        GetStartEndGraph $getStartEndGraph,
    ): Response {
        $this->base_data['content_wrapper']['titre'] = 'Search IX Stats';

        $form = $this->createForm(SearchIXForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->base_data['content_wrapper']['titre'] = \sprintf(
                'Top %s (%s)',
                $this->base_data['top'],
                '24 hours'
            );

            $this->base_data['content_wrapper']['small'] = $form->get('ix')->getData();

            $this->data['data'] = $asDataRepository::get(
                $this->base_data['top'],
                '',
                [],
                $peeringDBRepository->getIXMembers((int) $form->get('ix_hidden')->getData()),
            );

            $this->data['graph_size'] = [
                'width' => $this->configApplication::getAsStatsConfigGraph()['top_graph_width'],
                'height' => $this->configApplication::getAsStatsConfigGraph()['top_graph_height'],
            ];
            $this->data['selectedLinks'] = [];

            $this->data = \array_merge($this->data, $getStartEndGraph->get());

            return $this->render('pages/ix/search_ix/show.html.twig', [
                'base_data' => $this->base_data,
                'data' => $this->data,
                'knownlinks' => KnowlinksRepository::get(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('pages/ix/search_ix/index.html.twig', [
            'base_data' => $this->base_data,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/search/get-ixname',
        name: 'ix.search.get_ixname',
        methods: ['POST'],
    )]
    public function getIXName(
        Request $request,
        PeeringDBRepository $peeringDBRepository,
    ): JsonResponse {
        $req = GetJsonParameters::getAll($request);

        if (!\array_key_exists('name', $req)) {
            return new JsonResponse(['message' => 'Bad JSON request.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = $peeringDBRepository->getIXName($req['name']);

            if (200 !== $data['status_code']) {
                throw new \Exception('No data from API.');
            }

            $return = [];
            foreach ($data['response']['data'] as $value) {
                $return[] = [
                    'id' => $value['id'],
                    'name' => \sprintf('%s (%s / %s', $value['name'], $value['city'], $value['country']),
                ];
            }

            return new JsonResponse($return);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
