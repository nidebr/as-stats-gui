<?php

declare(strict_types=1);

namespace App\Client;

use App\Util\EndWithFunction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PeeringDbClient extends AbstractController
{
    public function __construct(
        readonly string $host,
        readonly string $uri,
        private HttpClientInterface $client,
    ) {
        if (!EndWithFunction::endsWith($uri, '/')) {
            $uri = \sprintf('%s/', $uri);
        }

        $this->client = $client->withOptions([
            'base_uri' => \sprintf('%s%s', $host, $uri),
            'verify_host' => false,
            'verify_peer' => false,
            'timeout' => 30,
            'max_duration' => 30,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function get(string $url): array
    {
        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            return [
                'status_code' => $response->getStatusCode(),
                'response' => $response->toArray(),
            ];
        } catch (ClientExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface $e) {
            if (429 === $e->getCode()) {
                $this->addFlash('warning', 'Request was throttled by peeringdb.com API server.');
            }

            return [
                'status_code' => $e->getCode(),
                'message' => $e->getMessage(),
                'response' => [],
            ];
        }
    }
}
