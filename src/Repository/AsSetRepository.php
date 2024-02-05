<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AsSetRepository
{
    private ConfigApplication $config;

    public function __construct(ConfigApplication $config)
    {
        $this->config = $config;
    }

    /**
     * @throws ConfigErrorException
     */
    public function getAsset(string $asset): array
    {
        /* sanity check */
        if (!\preg_match('/^[a-zA-Z0-9:_-]+$/', $asset)) {
            return [];
        }

        $cache = false;

        $assetfile = \sprintf('%s/%s.txt', $this->config::getAsStatsAssetPath(), $asset);

        if (\file_exists($assetfile)) {
            $filemtime = \filemtime($assetfile);
            if (!$filemtime || (\time() - $filemtime >= $this->config::getAsStatsAssetCacheLife())) {
                $list = $this->getWhois($asset);
            } else {
                $list = $this->readCacheFile($asset);
                $cache = true;
                if ([] === $list) {
                    $list = $this->getWhois($asset);
                }
            }
        } else {
            $list = $this->getWhois($asset);
        }

        return \array_merge(['cache' => $cache], $list);
    }

    /**
     * @throws ConfigErrorException
     */
    private function getWhois(string $asset): array
    {
        $process = new Process([$this->config::getAsStatsAssetWhois(), '-h', 'whois.radb.net', \sprintf('!i%s', $asset)]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $return_list = \explode(' ', \trim(\str_replace(PHP_EOL, ' ', $process->getOutput())));

        /* write cache file */
        $this->writeCacheFile($asset, $process->getOutput());

        return $this->parseOtherAsset($this->parseData($return_list));
    }

    private function parseData(array $asnlist): array
    {
        $return = [];
        foreach ($asnlist as $asn) {
            if (\str_starts_with($asn, 'AS')) {
                $return[] = $asn;
            }
        }

        return $return;
    }

    private function writeCacheFile(string $asset, string $asnlist): void
    {
        if ('' !== $asset && '0' !== $asset) {
            \file_put_contents(\sprintf('%s/%s.txt', $this->config::getAsStatsAssetPath(), $asset), $asnlist);
        }
    }

    private function readCacheFile(string $asset): array
    {
        $return = [];
        if ('' !== $asset && '0' !== $asset) {
            $input = \file_get_contents(\sprintf('%s/%s.txt', $this->config::getAsStatsAssetPath(), $asset));

            if (!$input) {
                return [];
            }

            $return = \explode(' ', \trim(\str_replace(PHP_EOL, ' ', $input)));
        }

        return $this->parseOtherAsset($this->parseData($return));
    }

    private function parseOtherAsset(array $aslist): array
    {
        $return = [];

        foreach ($aslist as $as) {
            $as_tmp = \substr($as, 2);
            if (\is_numeric($as_tmp)) {
                $return['as_num'][] = $as_tmp;
            } else {
                $return['as_other'][] = $as;
            }
        }

        return $return;
    }
}
