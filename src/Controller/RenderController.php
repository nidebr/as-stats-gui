<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\RRDRepository;
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
        name: 'render',
        methods: ['GET'],
    )]
    public function renderGraph(
        int $as,
        Request $request,
    ): Response {
        $cmd = new RRDRepository($as, $request->query->all());

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
