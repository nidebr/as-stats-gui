<?php

declare(strict_types=1);

namespace App\Twig;

use App\Util\Theme\Icon;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IconExtension extends AbstractExtension
{
    /**
     * @return array<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', [$this, 'icon'], ['is_safe' => ['html']]),
        ];
    }

    public function icon(string $name, ?string $addClass = null): string
    {
        $icon = Icon::byName($name);

        if ($addClass) {
            return \sprintf('<i class="ti %s %s"></i>', $icon->value, $addClass);
        }

        return \sprintf('<i class="ti %s"></i>', $icon->value);
    }
}
