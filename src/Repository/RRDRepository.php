<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\ConfigApplication;

class RRDRepository
{
    public static function getRRDFileForAS(int $as): string
    {
        return \sprintf(
            '%s/%s/%s.rrd',
            ConfigApplication::getAsStatsConfigGraph()['rrdpath'],
            \sprintf('%02x', $as % 256),
            $as
        );
    }
}
