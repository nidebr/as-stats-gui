<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\KnownLinksEmptyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class KnownLinksEmptyListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
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
        $exception = $event->getThrowable();

        if (!$exception instanceof KnownLinksEmptyException) {
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
