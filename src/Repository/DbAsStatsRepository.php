<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\DbErrorException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class DbAsStatsRepository
{
    private Connection $cnx;
    private string $dbname;

    /**
     * @throws Exception
     */
    public function __construct(string $dbname)
    {
        $dbParams = [
            'driver' => 'pdo_sqlite',
            'path' => $dbname,
        ];

        $this->cnx = DriverManager::getConnection($dbParams);
        $this->dbname = $dbname;
    }

    /**
     * @throws Exception
     * @throws DbErrorException
     */
    public function getASStatsTop(int $ntop, array $selected_links, array $list_asn = []): array
    {
        if ([] === $selected_links) {
            $selected_links = [];
            foreach (KnowlinksRepository::get() as $link) {
                $selected_links[] = $link['tag'];
            }
        }

        $query_total = '0';
        $query_links = '';

        foreach ($selected_links as $tag) {
            $query_links .= \sprintf('%1$s_in, %1$s_out, %1$s_v6_in, %1$s_v6_out, ', $tag);
            $query_total .= \sprintf(' + %1$s_in + %1$s_out + %1$s_v6_in + %1$s_v6_out', $tag);
        }

        try {
            if ([] !== $list_asn) {
                $asn = $this->cnx->createQueryBuilder()
                    ->select(\sprintf('asn, %s %s as total', $query_links, $query_total))
                    ->from('stats')
                    ->where(\sprintf('asn IN (%s)', \implode(',', $list_asn)))
                    ->orderBy('total', 'DESC')
                    ->setMaxResults($ntop)
                    ->fetchAllAssociative();
            } else {
                $asn = $this->cnx->createQueryBuilder()
                    ->select(\sprintf('asn, %s %s as total', $query_links, $query_total))
                    ->from('stats')
                    ->orderBy('total', 'DESC')
                    ->setMaxResults($ntop)
                    ->fetchAllAssociative();
            }
        } catch (Exception $e) {
            throw new DbErrorException(\sprintf('Problem with stats files %s', $this->dbname));
        }

        $asstats = [];
        foreach ($asn as $row) {
            $tot_in = 0;
            $tot_out = 0;
            $tot_v6_in = 0;
            $tot_v6_out = 0;

            foreach ($row as $key => $value) {
                if (\str_contains($key, '_in')) {
                    if (\str_contains($key, '_v6_')) {
                        $tot_v6_in += $value;
                    } else {
                        $tot_in += $value;
                    }
                } elseif (\str_contains($key, '_out')) {
                    if (\str_contains($key, '_v6_')) {
                        $tot_v6_out += $value;
                    } else {
                        $tot_out += $value;
                    }
                }
            }

            $asstats[$row['asn']] = [$tot_in, $tot_out, $tot_v6_in, $tot_v6_out];
        }

        return $asstats;
    }
}
