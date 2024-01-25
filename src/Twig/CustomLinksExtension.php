<?php

declare(strict_types=1);

namespace App\Twig;

use App\Repository\CustomLinksRepository;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomLinksExtension extends AbstractExtension
{
    private Environment $templating;
    private CustomLinksRepository $customLinksRepository;

    public function __construct(
        Environment $templating,
        CustomLinksRepository $customLinksRepository,
    ) {
        $this->templating = $templating;
        $this->customLinksRepository = $customLinksRepository;
    }

    /**
     * @return array<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('custom_links_top', [$this, 'customLinkTop'], ['is_safe' => ['html']]),
            new TwigFunction('custom_links_history', [$this, 'customLinkHistory'], ['is_safe' => ['html']]),
        ];
    }

    public function customLinkTop(
        int $as,
    ): string {
        return $this->templating->render(
            'core/custom_links_top.html.twig',
            ['links' => $this->customLinksRepository->getLink($as)]
        );
    }

    public function customLinkHistory(
        int $as,
    ): string {
        return $this->templating->render(
            'core/custom_links_history.html.twig',
            ['links' => $this->customLinksRepository->getLink($as)]
        );
    }
}
