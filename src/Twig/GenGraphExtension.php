<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GenGraphExtension extends AbstractExtension
{
    public function __construct(private RouterInterface $router)
    {
    }

    /**
     * @return array<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('gen_graph', [$this, 'genGraph'], ['is_safe' => ['html']]),
        ];
    }

    public function genGraph(
        int $as,
        int $ipversion,
        string $title,
        int $start,
        int $end,
        string $selectedLinks,
        ?int $width = null,
        ?int $height = null,
        bool $legend = true,
    ): string {
        return \sprintf(
            '<img alt="Graph IPv%s for AS%s" src="%s">',
            $ipversion,
            $as,
            $this->router->generate(
                'render',
                [
                    'as' => $as,
                    'v' => $ipversion,
                    'title' => $title,
                    'legend' => $legend,
                    'start' => $start,
                    'end' => $end,
                    'width' => $width,
                    'height' => $height,
                    'selected_links' => $selectedLinks,
                ]
            )
        );
    }
}
