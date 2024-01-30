<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\ConfigApplication;
use App\Util\RRDGraph;

class RRDAsnRepository
{
    private array $request;
    private array $knowlinks;
    private string $rrdfile;
    private string $v6;
    private RRDGraph $rrdGraph;

    public function __construct(int $as, array $req)
    {
        $this->request = $req;

        $this->rrdGraph = new RRDGraph($this->request);
        $this->knowlinks = $this->selectedLinks();
        $this->rrdfile = $this->rrdGraph->getRRDFileForAS($as);
        $this->v6 = $this->rrdGraph->addV6Graph();
    }

    private function selectedLinks(): array
    {
        $knownlinks = KnowlinksRepository::get();

        if (isset($this->request['selected_links']) && '' !== $this->request['selected_links']) {
            $reverse = [];

            foreach ($knownlinks as $link) {
                $reverse[$link['tag']] = [
                    'color' => $link['color'],
                    'descr' => $link['descr'],
                ];
            }

            $links = [];
            foreach (\explode(',', \sprintf('%s', $this->request['selected_links'])) as $tag) {
                if (\preg_match('/[^a-zA-Z0-9_]/', $tag)) {
                    continue;
                }

                if (!isset($reverse[$tag])) {
                    continue;
                }

                $links[] = [
                    'tag' => $tag,
                    'color' => $reverse[$tag]['color'],
                    'descr' => $reverse[$tag]['descr'],
                ];
            }

            $knownlinks = $links;
        }

        return $knownlinks;
    }

    private function addData(): string
    {
        $cmd = '';

        foreach ($this->knowlinks as $link) {
            $cmd .= \sprintf('DEF:%1$s_%2$sin="%3$s":%1$s_%2$sin:AVERAGE ', $link['tag'], $this->v6, $this->rrdfile);
            $cmd .= \sprintf('DEF:%1$s_%2$sout="%3$s":%1$s_%2$sout:AVERAGE ', $link['tag'], $this->v6, $this->rrdfile);
        }

        $tot_in_bits = 'CDEF:tot_in_bits=0';
        $tot_out_bits = 'CDEF:tot_out_bits=0';

        /* generate a CDEF for each DEF to multiply by 8 (bytes to bits), and reverse for outbound */
        foreach ($this->knowlinks as $link) {
            $cmd .= \sprintf('CDEF:%1$s_%2$sin_bits_pos=%1$s_%2$sin,8,* ', $link['tag'], $this->v6);
            $cmd .= \sprintf('CDEF:%1$s_%2$sout_bits_pos=%1$s_%2$sout,8,* ', $link['tag'], $this->v6);
            $tot_in_bits .= \sprintf(',%s_%sin_bits_pos,ADDNAN', $link['tag'], $this->v6);
            $tot_out_bits .= \sprintf(',%s_%sout_bits_pos,ADDNAN', $link['tag'], $this->v6);
        }

        $cmd .= \sprintf('%s ', $tot_in_bits);
        $cmd .= \sprintf('%s ', $tot_out_bits);

        $cmd .= 'VDEF:tot_in_bits_95th_pos=tot_in_bits,95,PERCENT ';
        $cmd .= 'VDEF:tot_out_bits_95th_pos=tot_out_bits,95,PERCENT ';

        if (ConfigApplication::getAsStatsConfigGraph()['outispositive']) {
            $cmd .= 'CDEF:tot_in_bits_95th=tot_in_bits,POP,tot_in_bits_95th_pos,-1,* ';
            $cmd .= 'CDEF:tot_out_bits_95th=tot_out_bits,POP,tot_out_bits_95th_pos,1,* ';
        } else {
            $cmd .= 'CDEF:tot_in_bits_95th=tot_in_bits,POP,tot_in_bits_95th_pos,1,* ';
            $cmd .= 'CDEF:tot_out_bits_95th=tot_out_bits,POP,tot_out_bits_95th_pos,-1,* ';
        }

        foreach ($this->knowlinks as $link) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive']) {
                $cmd .= \sprintf('CDEF:%1$s_%2$sin_bits=%1$s_%2$sin_bits_pos,-1,* ', $link['tag'], $this->v6);
                $cmd .= \sprintf('CDEF:%1$s_%2$sout_bits=%1$s_%2$sout_bits_pos,1,* ', $link['tag'], $this->v6);
            } else {
                $cmd .= \sprintf('CDEF:%1$s_%2$sin_bits=%1$s_%2$sin_bits_pos,1,* ', $link['tag'], $this->v6);
                $cmd .= \sprintf('CDEF:%1$s_%2$sout_bits=%1$s_%2$sout_bits_pos,-1,* ', $link['tag'], $this->v6);
            }
        }

        return $cmd;
    }

    private function generateStackAreaInbound(): string
    {
        $cmd = '';
        $i = 0;
        foreach ($this->knowlinks as $link) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive'] && ConfigApplication::getAsStatsConfigGraph()['brighten_negative']) {
                $col = \sprintf('%sBB', $link['color']);
            } else {
                $col = $link['color'];
            }

            $descr = \str_replace(':', '\:', $link['descr']); // Escaping colons in description
            $cmd .= \sprintf('AREA:%s_%sin_bits#%s:"%s"', $link['tag'], $this->v6, $col, $descr);

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
        foreach ($this->knowlinks as $link) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive'] || !ConfigApplication::getAsStatsConfigGraph()['brighten_negative']) {
                $col = $link['color'];
            } else {
                $col = \sprintf('%sBB', $link['color']);
            }

            $cmd .= \sprintf('AREA:%s_%sout_bits#%s:', $link['tag'], $this->v6, $col);

            if ($i > 0) {
                $cmd .= ':STACK';
            }

            $cmd .= ' ';
            ++$i;
        }

        return $cmd;
    }

    private function add95th(): string
    {
        $cmd = '';
        if (ConfigApplication::getAsStatsConfigGraph()['show95th']) {
            $cmd .= 'LINE1:tot_in_bits_95th#FF0000 ';
            $cmd .= 'LINE1:tot_out_bits_95th#FF0000 ';
            $cmd .= 'GPRINT:tot_in_bits_95th_pos:\'95th in %6.2lf%s\' ';
            $cmd .= 'GPRINT:tot_out_bits_95th_pos:\'/ 95th out %6.2lf%s\\n\' ';
        }

        return $cmd;
    }

    public function generateCmd(): string
    {
        $graphSize = $this->rrdGraph->getGraphSize();

        return \sprintf(
            '%s graph - --slope-mode --alt-autoscale --upper-limit 0 --lower-limit 0 --imgformat=PNG \
            --base=1000 --height=%s --width=%s --alt-autoscale-max --full-size-mode \
            --color BACK#ffffff00 --color SHADEA#ffffff00 --color SHADEB#ffffff00 \
            %s %s %s %s %s %s %s COMMENT:\' \\n\' %s HRULE:0#00000080',
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
            $this->add95th(),
        );
    }
}
