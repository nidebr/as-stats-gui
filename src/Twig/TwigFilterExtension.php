<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigFilterExtension extends AbstractExtension
{
    /**
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_bytes', [$this, 'formatBytes']),
            new TwigFilter('concat_link', [$this, 'concatSelectedLinks']),
        ];
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1099511627776) {
            return \sprintf('%.2f TB', $bytes / 1099511627776);
        }

        if ($bytes >= 1073741824) {
            return \sprintf('%.2f GB', $bytes / 1073741824);
        }

        if ($bytes >= 1048576) {
            return \sprintf('%.2f MB', $bytes / 1048576);
        }

        if ($bytes >= 1024) {
            return \sprintf('%d KB', $bytes / 1024);
        }

        return \sprintf('%s bytes', $bytes);
    }

    public function concatSelectedLinks(array $selectedLinks): string
    {
        $result = '';
        foreach ($selectedLinks as $link) {
            $result .= \sprintf('%s,', $link);
        }

        return rtrim($result, ',');
    }
}
