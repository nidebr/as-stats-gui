<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ConfigErrorException;
use App\Exception\KnownLinksEmptyException;
use App\Repository\KnowlinksRepository;
use App\Util\Annotation\Menu;
use App\Util\GetStartEndGraph;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    path: '/links/usage',
)]
#[Menu('links_usage')]
class LinksUsageController extends BaseController
{
    protected array $data = [];

    /**
     * @throws ConfigErrorException
     * @throws KnownLinksEmptyException
     */
    #[Route(
        path: '/',
        name: 'links.usage',
        methods: ['GET'],
    )]
    public function index(
        GetStartEndGraph $getStartEndGraph,
    ): Response {
        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'Top %s AS - per link usage (%s)',
            $this->configApplication::getLinksUsageTop(),
            '24 hours'
        );

        return $this->render('pages/link_usage/index.html.twig', [
            'base_data' => $this->base_data,
            'knownlinks' => KnowlinksRepository::get(),
            'data' => $getStartEndGraph->get(),
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
