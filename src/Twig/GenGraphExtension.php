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

    public function genGraph(int $as, int $ipversion): string
    {
        return \sprintf(
            '<img class="img-fuild" alt="Graph IPv%s for AS%s" src="%s">',
            $ipversion,
            $as,
            $this->router->generate('render', ['as' => $as, 'v' => 4, 'title' => 'dede', 'nolegend' => 1, 'start' => 1705921337, 'end' => 1706007737, 'width' => 600, 'height' => 220])
        );
    }
}
