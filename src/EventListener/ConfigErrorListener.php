<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class ConfigErrorListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ConfigApplication $config,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 2],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->config->isDev()) {
            return;
        }

        $exception = $event->getThrowable();

        if (!$exception instanceof ConfigErrorException) {
            return;
        }

        $response = new Response(
            $this->twig->render('bundles/TwigBundle/Exception/error.html.twig', [
                'status_code' => 500,
                'status_text' => \sprintf('Problem on asstats.yml : %s', $exception->getMessage()),
            ]),
            500,
        );

        $event->setResponse($response);
    }
}
