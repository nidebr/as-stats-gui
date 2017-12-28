<?php

namespace Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorProvider implements ServiceProviderInterface
{
  public function register(Container $app)
  {
    $app->error(function (\Exception $e, Request $request, $code) use ($app) {
        if ($app['debug']) {
            return;
        }

        // 404.html, or 40x.html, or 4xx.html, or error.html
        $templates = array(
            'errors/' . $code . '.html.twig',
            'errors/' . substr($code, 0, 2) . 'x.html.twig',
            'errors/' . substr($code, 0, 1) . 'xx.html.twig',
            'errors/default.html.twig',
        );

        $error = $e->getMessage();
        return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code, 'error' => $error)), $code);
    });
  }
}
