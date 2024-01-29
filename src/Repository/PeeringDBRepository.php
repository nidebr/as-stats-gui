<?php

declare(strict_types=1);

namespace App\Repository;

use App\Client\PeeringDbClient;

class PeeringDBRepository
{
    private PeeringDbClient $peeringDbClient;

    public function __construct(PeeringDbClient $peeringDbClient)
    {
        $this->peeringDbClient = $peeringDbClient;
    }

    public function getIXMembers(int $ix_id): array
    {
        $data = $this->peeringDbClient->get(\sprintf('net?ix_id=%s', $ix_id));

        if (200 !== $data['status_code']) {
            return [];
        }

        $asn = [];
        foreach ($data['response']['data'] as $list) {
            $asn[] = $list['asn'];
        }

        return $asn;
    }

    public function getIXInfo(int $ix_id): array
    {
        $data = $this->peeringDbClient->get(\sprintf('ix?id=%s', $ix_id));

        if (200 !== $data['status_code']) {
            return [];
        }

        return $data['response']['data'][0];
    }

    public function getIXName(string $regex): array
    {
        if ('' === $regex || '0' === $regex) {
            return [];
        }

        return $this->peeringDbClient->get(\sprintf('ix?name__contains=%s', $regex));
    }
}
