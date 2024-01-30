<?php

declare(strict_types=1);

namespace App\Util;

use App\Application\ConfigApplication;

final class RRDGraph
{
    private array $request;

    public function __construct(array $req)
    {
        $this->request = $req;
    }

    public function getGraphSize(): array
    {
        $width = ConfigApplication::getAsStatsConfigGraph()['default_graph_width'];
        $height = ConfigApplication::getAsStatsConfigGraph()['default_graph_height'];

        if (isset($this->request['width'])) {
            $width = (int) $this->request['width'];
        }

        if (isset($this->request['height'])) {
            $height = (int) $this->request['height'];
        }

        return [
            'width' => $width,
            'height' => $height,
        ];
    }

    public function addV6Graph(): string
    {
        if ('6' === $this->request['v']) {
            return 'v6_';
        }

        return '';
    }

    public function addLegend(): string
    {
        if (isset($this->request['legend']) && '0' === $this->request['legend']) {
            return '--no-legend ';
        }

        return '';
    }

    public function verticalLabel(): string
    {
        if (ConfigApplication::getAsStatsConfigGraph()['vertical_label']) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive']) {
                return '--vertical-label \'<- IN | OUT ->\' ';
            }

            return '--vertical-label \'<- OUT | IN ->\' ';
        }

        return '';
    }

    public function addStartEnd(): string
    {
        $cmd = '';

        if (isset($this->request['start']) && is_numeric($this->request['start'])) {
            $cmd .= \sprintf('--start %s ', $this->request['start']);
        }

        if (isset($this->request['end']) && is_numeric($this->request['end'])) {
            $cmd .= \sprintf('--end %s ', $this->request['end']);
        }

        return $cmd;
    }

    public function addTitle(): string
    {
        $cmd = '';

        if (ConfigApplication::getAsStatsConfigGraph()['showtitledetail'] && isset($this->request['title']) && '' !== $this->request['title']) {
            $cmd .= \sprintf('--title %s ', \escapeshellarg(\sprintf('%s', $this->request['title'])));
        } elseif (isset($this->request['v']) && is_numeric($this->request['v'])) {
            $cmd .= \sprintf('--title IPv%s ', $this->request['v']);
        }

        return $cmd;
    }

    public function getRRDFileForAS(int $as): string
    {
        return \sprintf(
            '%s/%s/%s.rrd',
            ConfigApplication::getAsStatsConfigGraph()['rrdpath'],
            \sprintf('%02x', $as % 256),
            $as
        );
    }
}
