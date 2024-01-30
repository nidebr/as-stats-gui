<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\ConfigApplication;
use App\Util\RRDGraph;

class RRDLinksUsageRepository
{
    private string $link;
    private array $request;
    private array $topas;
    private array $colors;
    // private array $knowlinks;
    // private string $rrdfile;
    private string $v6;
    private RRDGraph $rrdGraph;

    public function __construct(string $link, array $req, array $topas)
    {
        $this->link = $link;
        $this->request = $req;
        $this->topas = $topas;

        //dump($topas);

        $this->rrdGraph = new RRDGraph($this->request);
        // $this->knowlinks = $this->selectedLinks();
        // $this->rrdfile = $this->getRRDFileForAS();
        $this->v6 = $this->rrdGraph->addV6Graph();
        $this->colors = ConfigApplication::getLinksUsageColor();
    }

    private function addData(): string
    {
        $cmd = '';

        foreach ($this->topas['asinfo'] as $as => $value) {
            $rrdfile = $this->rrdGraph->getRRDFileForAS($as);
            $cmd .= \sprintf('DEF:as%1$s_%2$sin="%3$s":%4$s_%2$sin:AVERAGE ', $as, $this->v6, $rrdfile, $this->link);
            $cmd .= \sprintf('DEF:as%1$s_%2$sout="%3$s":%4$s_%2$sout:AVERAGE ', $as, $this->v6, $rrdfile, $this->link);
        }

        foreach ($this->topas['asinfo'] as $as => $value) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive']) {
                $cmd .= \sprintf('CDEF:as%1$s_%2$sin_bits=as%1$s_%2$sin,-8,* ', $as, $this->v6);
                $cmd .= \sprintf('CDEF:as%1$s_%2$sout_bits=as%1$s_%2$sout,8,* ', $as, $this->v6);
            } else {
                $cmd .= \sprintf('CDEF:as%1$s_%2$sin_bits=as%1$s_%2$sin,8,* ', $as, $this->v6);
                $cmd .= \sprintf('CDEF:as%1$s_%2$sout_bits=as%1$s_%2$sout,-8,* ', $as, $this->v6);
            }
        }

        return $cmd;
    }

    private function generateStackAreaInbound(): string
    {
        $cmd = '';
        $i = 0;

        foreach ($this->topas['asinfo'] as $as => $value) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive'] && ConfigApplication::getAsStatsConfigGraph()['brighten_negative']) {
                $col = \sprintf('%sBB', $this->colors[$i]);
            } else {
                $col = $this->colors[$i];
            }

            $descr = \str_replace(':', '\:', $value['info']['description']); // Escaping colons in description
            $cmd .= \sprintf('AREA:as%1$s_%2$sin_bits#%3$s:"AS%1$s (%4$s)\\n"', $as, $this->v6, $col, $descr);

            if ($i > 0) {
                $cmd .= ':STACK';
            }
            $cmd .= ' ';
            ++$i;
        }

        return $cmd;
    }

    private function generateStackAreaOutbound(): string
    {
        $cmd = '';
        $i = 0;

        foreach ($this->topas['asinfo'] as $as => $value) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive'] || !ConfigApplication::getAsStatsConfigGraph()['brighten_negative']) {
                $col = $this->colors[$i];
            } else {
                $col = \sprintf('%sBB', $this->colors[$i]);
            }

            $cmd .= \sprintf('AREA:as%s_%sout_bits#%s:', $as, $this->v6, $col);

            if ($i > 0) {
                $cmd .= ':STACK';
            }
            $cmd .= ' ';
            ++$i;
        }

        return $cmd;
    }

    public function generateCmd(): string
    {
        $graphSize = $this->rrdGraph->getGraphSize();

        return \sprintf(
            '%s graph - --slope-mode --alt-autoscale --upper-limit 0 --lower-limit 0 --imgformat=PNG \
            --base=1000 --height=%s --width=%s --full-size-mode \
            --color BACK#ffffff00 --color SHADEA#ffffff00 --color SHADEB#ffffff00 \
            %s %s %s %s %s %s %s HRULE:0#00000080',
            ConfigApplication::getAsStatsConfigGraph()['rrdtool'],
            $graphSize['height'],
            $graphSize['width'],
            $this->rrdGraph->verticalLabel(),
            $this->rrdGraph->addTitle(),
            $this->rrdGraph->addLegend(),
            $this->rrdGraph->addStartEnd(),
            $this->addData(),
            $this->generateStackAreaInbound(),
            $this->generateStackAreaOutbound(),
        );
    }
}
