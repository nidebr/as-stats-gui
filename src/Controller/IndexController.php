<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ConfigApplication;
use App\Util\Annotation\Menu;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Menu('top_as')]
class IndexController extends BaseController
{
    #[Route(
        path: '/',
        name: 'index',
        methods: ['GET'],
    )]
    public function index(
        ConfigApplication $Config,
    ): Response
    {
        /*if(\isset($this->base_data['request']['top'])) {
            $top = $this->base_data['request']['top'];
        } else {
            $top = $Config::getAsStatsConfig()['top'];
        }*/

        dump($this->base_data['request']);

        /*if (\array_key_exists('top', $this->base_data['request'])) {
            dump(1);
        } else {
            dump(2);
        }*/

        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'Top %s (%s)',
            \array_key_exists('top', $this->base_data['request']) :: $this->base_data['request']['top'] ?? $Config::getAsStatsConfig()['top'],
            '24 hours'
        );

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
        ]);
    }

    #[Route(
        path: '/{topinterval}',
        name: 'index_topinterval',
        methods: ['GET'],
    )]
    public function indexTopInterval(
        ConfigApplication $Config,
        string $topinterval,
    ): Response{
        $this->base_data['content_wrapper']['titre'] = \sprintf(
            'Top %s (%s)',
            $Config::getAsStatsConfig()['top'],
            $Config::getAsStatsTopInterval()[$topinterval]['label']
        );

        return $this->render('pages/index.html.twig', [
            'base_data' => $this->base_data,
        ]);
    }
}
