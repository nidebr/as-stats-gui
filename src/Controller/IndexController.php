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
            $data = $asDataRepository::get($this->base_data['top'], null, (array) $form->getData());
            $selectedLinks = KnowlinksRepository::select((array) $form->getData());
        } else {
            $data = $asDataRepository::get($this->base_data['top']);
            $selectedLinks = [];
        }

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
            'data' => $data,
            'knownlinks' => KnowlinksRepository::get(),
            'selected_links' => $selectedLinks,
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
            $data = $asDataRepository::get($this->base_data['top'], $topinterval, (array) $form->getData());
        } else {
            $data = $asDataRepository::get($this->base_data['top'], $topinterval);
        }

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
            'data' => $data,
            'hours' => $Config::getAsStatsConfigTopInterval()[$topinterval]['label'],
            'knownlinks' => KnowlinksRepository::get(),
            'form' => [
                'legend' => $form->createView(),
            ],
        ]);
    }
}
