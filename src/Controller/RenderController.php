<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use App\Exception\DbErrorException;
use App\Repository\GetAsDataRepository;
use App\Repository\RRDAsnRepository;
use App\Repository\RRDLinksUsageRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    path: '/render',
)]
class RenderController extends AbstractController
{
    #[Route(
        path: '/graph/{as}',
        name: 'render.graph.as',
        methods: ['GET'],
    )]
    public function renderGraph(
        int $as,
        Request $request,
    ): Response {
        $cmd = new RRDAsnRepository($as, $request->query->all());

        $response = new Response();
        $response->headers->set('Content-type', 'image/png');
        $response->sendHeaders();
        $response->setContent(
            \sprintf(
                '%s',
                passthru($cmd->generateCmd())
            )
        );

        return $response;
    }

    /**
     * @throws ConfigErrorException
     * @throws DbErrorException
     * @throws Exception
     */
    #[Route(
        path: '/links/usage/graph/{link}',
        name: 'render.links.usage.graph',
        methods: ['GET'],
    )]
    public function renderGraphLinksUsage(
        string $link,
        Request $request,
        GetAsDataRepository $asDataRepository,
    ): Response {
        $cmd = new RRDLinksUsageRepository(
            $link,
            $request->query->all(),
            $asDataRepository::get(ConfigApplication::getLinksUsageTop(), '', [$link => true])
        );

        $response = new Response();
        $response->headers->set('Content-type', 'image/png');
        $response->sendHeaders();
        $response->setContent(
            \sprintf(
                '%s',
                passthru($cmd->generateCmd())
            )
        );

        return $response;
    }
}
