<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchASForm;
use App\Repository\DbAsInfoRepository;
use App\Util\Annotation\Menu;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    path: '/history',
)]

#[Menu('view_as')]
class HistoryController extends BaseController
{
    protected array $data = [];

    #[Route(
        path: '/',
        name: 'history',
        methods: ['GET|POST'],
    )]
    public function history(
        Request $request,
    ): Response {
        $this->base_data['content_wrapper']['titre'] = 'Search history for AS';

        $form = $this->createForm(SearchASForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('history_as', [
                'as' => $form->get('as')->getData(),
            ]);
        }

        return $this->render('pages/history/history.html.twig', [
            'base_data' => $this->base_data,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/{as}',
        name: 'history_as',
        methods: ['GET'],
    )]
    public function historyAs(
        int $as,
        DbAsInfoRepository $asInfoRepository
    ): Response {
        $this->data['as'] = $as;

        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'History for AS%s',
            $this->data['as'],
        );

        $this->base_data['content_wrapper']['small'] = \sprintf(
            '%s',
            $asInfoRepository->getAsInfo($this->data['as'])['name'],
        );

        $form = $this->createForm(SearchASForm::class);

        return $this->render('pages/history/history_as.html.twig', [
            'base_data' => $this->base_data,
            'data' => $this->data,
            'form' => $form->createView(),
        ]);
    }
}
