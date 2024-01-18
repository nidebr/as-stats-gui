<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use App\Exception\DbErrorException;
use Doctrine\DBAL\Exception;

class GetAsDataRepository
{
    /**
     * @throws ConfigErrorException
     * @throws Exception
     * @throws DbErrorException
     */
    public static function get(int $top): array
    {
        if (!$top) {
            return [];
        }

        $return = [];

        $data = new DbAsStatsRepository(ConfigApplication::getAsStatsConfigDayStatsFile());
        $asInfoRepository = new DbAsInfoRepository();

        foreach ($data->getASStatsTop($top, []) as $as => $nbytes) {
            $return['asinfo'][$as]['info'] = $asInfoRepository->getAsInfo($as);

            $return['asinfo'][$as]['v4'] = [
                'in' => $nbytes[0],
                'out' => $nbytes[1],
            ];

            if (ConfigApplication::getAsStatsConfigGraph()['showv6']) {
                $return['asinfo'][$as]['v6'] = [
                    'in' => $nbytes[2],
                    'out' => $nbytes[3],
                ];
            }

            $return['customlinks'][$as] = CustomLinksRepository::generate($as);
        }

        return $return;
    }
}
