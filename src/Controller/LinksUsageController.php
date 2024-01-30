<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\KnowlinksRepository;
use App\Util\Annotation\Menu;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    path: '/links/usage',
)]
#[Menu('links_usage')]
class LinksUsageController extends BaseController
{
    protected array $data = [];

    #[Route(
        path: '/',
        name: 'links.usage',
        methods: ['GET'],
    )]
    public function index(): Response
    {
        return $this->render('pages/link_usage/index.html.twig', [
            'base_data' => $this->base_data,
            'knownlinks' => KnowlinksRepository::get(),
        ]);
    }

    #[Route(
        path: '/{topinterval}',
        name: 'links.usage.topinterval',
        methods: ['GET'],
    )]
    public function indexTopInterval(
        string $topinterval,
    ): Response {
        return $this->render('pages/link_usage/index.html.twig', [
            'base_data' => $this->base_data,
        ]);
    }
}
