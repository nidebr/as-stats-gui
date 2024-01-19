<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\ConfigApplication;

class CustomLinksRepository
{
    public static function generate(int $as): string
    {
        try {
            $htmllinks = [];

            foreach (ConfigApplication::getAsStatsConfigCustomLinks() as $linkname => $url) {
                $url = \str_replace('%as%', \sprintf('%s', $as), $url);
                $htmllinks[] = \sprintf('<a href="%s" target="_blank">%s</a>', $url, \htmlspecialchars($linkname));
            }

            return \implode(' | ', $htmllinks);
        } catch (\Exception) {
            return '';
        }
    }
}
