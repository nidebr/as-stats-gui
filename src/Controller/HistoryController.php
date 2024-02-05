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
            return $this->redirectToRoute('history.as', [
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
        name: 'history.as',
        methods: ['GET'],
    )]
    public function historyAs(
        int $as,
        DbAsInfoRepository $asInfoRepository
    ): Response {
        $this->data['as'] = $as;
        $this->data['asinfo'] = $asInfoRepository->getAsInfo($this->data['as']);

        if ('UNKNOWN' === $this->data['asinfo']['name']) {
            $this->addFlash('error', \sprintf('Unable to find AS%s in database ASInfo, please update.', $this->data['as']));
        }

        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'History for AS%s',
            $this->data['as'],
        );

        $this->base_data['content_wrapper']['small'] = \sprintf(
            '%s',
            $this->data['asinfo']['name'],
        );

        $this->data['end'] = time();
        $this->data['start']['daily'] = time() - 24 * 3600;
        $this->data['start']['weekly'] = time() - 6.9 * 86400;
        $this->data['start']['monthly'] = time() - 30 * 86400;
        $this->data['start']['yearly'] = time() - 365 * 86400;

        return $this->render('pages/history/history_as.html.twig', [
            'base_data' => $this->base_data,
            'data' => $this->data,
            'form' => $this->createForm(SearchASForm::class)->createView(),
        ]);
    }
}
