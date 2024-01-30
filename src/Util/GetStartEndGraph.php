<?php

declare(strict_types=1);

namespace App\Util;

use App\Application\ConfigApplication;

final class GetStartEndGraph
{
    public function get(string $topinterval = ''): array
    {
        $hours = 24;

        try {
            if ('' !== $topinterval && '0' !== $topinterval) {
                $hours = ConfigApplication::getAsStatsConfigTopInterval()[$topinterval]['hours'];
            }

            return [
                'start' => time() - $hours * 3600,
                'end' => time(),
            ];
        } catch (\Exception) {
            return [
                'start' => time() - $hours * 3600,
                'end' => time(),
            ];
        }
    }
}
