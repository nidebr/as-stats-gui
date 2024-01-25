<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\ConfigApplication;

class CustomLinksRepository
{
    public function getLink(int $as): array
    {
        try {
            $htmllinks = [];

            foreach (ConfigApplication::getAsStatsConfigCustomLinks() as $linkname => $url) {
                $url = \str_replace('%as%', \sprintf('%s', $as), $url);

                $htmllinks[] = [
                    'url' => $url,
                    'linkname' => \htmlspecialchars($linkname),
                ];
            }

            return $htmllinks;
        } catch (\Exception) {
            return [];
        }
    }
}
