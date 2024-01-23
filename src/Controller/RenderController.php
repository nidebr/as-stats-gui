<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use App\Exception\KnownLinksEmptyException;
use App\Repository\KnowlinksRepository;
use App\Repository\RRDRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RenderController extends AbstractController
{
    /**
     * @throws ConfigErrorException
     * @throws KnownLinksEmptyException
     */
    #[Route(
        path: '/render/graph',
        name: 'render',
        methods: ['GET'],
    )]
    public function renderGraph(Request $request): Response
    {
        $req = $request->query->all();
        $as = (int) $req['as'];

        if (!\preg_match('/^\d+$/', \sprintf('%s', $as))) {
            die('Invalid AS');
        }

        $width = ConfigApplication::getAsStatsConfigGraph()['default_graph_width'];
        $height = ConfigApplication::getAsStatsConfigGraph()['default_graph_height'];

        if (isset($req['width'])) {
            $width = (int) $req['width'];
        }

        if (isset($req['height'])) {
            $height = (int) $req['height'];
        }

        $v6_el = '';
        if ($req['v'] === '6') {
            $v6_el = 'v6_';
        }

        $knownlinks = KnowlinksRepository::get();

        if (isset($req['selected_links']) && $req['selected_links'] !== '') {
            $reverse = [];

            foreach ($knownlinks as $link) {
                $reverse[$link['tag']] = [
                    'color' => $link['color'],
                    'descr' => $link['descr'],
                ];
            }

            $links = [];
            foreach (\explode(',', \sprintf('%s', $req['selected_links'])) as $tag) { // @phpstan-ignore-line
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

        $rrdfile = RRDRepository::getRRDFileForAS($as);

        $cmd = \sprintf(
            '%s graph - --slope-mode --alt-autoscale --upper-limit 0 --lower-limit 0 --imgformat=PNG \
            --base=1000 --height=%s --width=%s --alt-autoscale-max --full-size-mode \
            --color BACK#ffffff00 --color SHADEA#ffffff00 --color SHADEB#ffffff00 ',
            ConfigApplication::getAsStatsConfigGraph()['rrdtool'],
            $height,
            $width
        );

        if (ConfigApplication::getAsStatsConfigGraph()['vertical_label']) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive']) {
                $cmd .= '--vertical-label \'<- IN | OUT ->\' ';
            } else {
                $cmd .= '--vertical-label \'<- OUT | IN ->\' ';
            }
        }

        if (ConfigApplication::getAsStatsConfigGraph()['showtitledetail'] && isset($req['title']) && $req['title'] !== '') {
            /** @phpstan-ignore-next-line */
            $cmd .= \sprintf('--title %s ', \escapeshellarg(\sprintf('%s', $req['title'])));
        } elseif (isset($req['v']) && is_numeric($req['v'])) {
            $cmd .= \sprintf('--title IPv%s ', $req['v']);
        }

        if (isset($req['nolegend']) && $req['nolegend'] === '1') {
            $cmd .= '--no-legend ';
        }

        if (isset($req['start']) && is_numeric($req['start'])) {
            $cmd .= \sprintf('--start %s ', $req['start']);
        }

        if (isset($req['end']) && is_numeric($req['end'])) {
            $cmd .= \sprintf('--end %s ', $req['end']);
        }

        foreach ($knownlinks as $link) {
            $cmd .= \sprintf('DEF:%1$s_%2$sin="%3$s":%1$s_%2$sin:AVERAGE ', $link['tag'], $v6_el, $rrdfile);
            $cmd .= \sprintf('DEF:%1$s_%2$sout="%3$s":%1$s_%2$sout:AVERAGE ', $link['tag'], $v6_el, $rrdfile);
        }

        $tot_in_bits = 'CDEF:tot_in_bits=0';
        $tot_out_bits = 'CDEF:tot_out_bits=0';

        /* generate a CDEF for each DEF to multiply by 8 (bytes to bits), and reverse for outbound */
        foreach ($knownlinks as $link) {
            $cmd .= \sprintf('CDEF:%1$s_%2$sin_bits_pos=%1$s_%2$sin,8,* ', $link['tag'], $v6_el);
            $cmd .= \sprintf('CDEF:%1$s_%2$sout_bits_pos=%1$s_%2$sout,8,* ', $link['tag'], $v6_el);
            $tot_in_bits .= \sprintf(',%s_%sin_bits_pos,ADDNAN', $link['tag'], $v6_el);
            $tot_out_bits .= \sprintf(',%s_%sout_bits_pos,ADDNAN', $link['tag'], $v6_el);
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

        foreach ($knownlinks as $link) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive']) {
                $cmd .= \sprintf('CDEF:%1$s_%2$sin_bits=%1$s_%2$sin_bits_pos,-1,* ', $link['tag'], $v6_el);
                $cmd .= \sprintf('CDEF:%1$s_%2$sout_bits=%1$s_%2$sout_bits_pos,1,* ', $link['tag'], $v6_el);
            } else {
                $cmd .= \sprintf('CDEF:%1$s_%2$sin_bits=%1$s_%2$sin_bits_pos,1,* ', $link['tag'], $v6_el);
                $cmd .= \sprintf('CDEF:%1$s_%2$sout_bits=%1$s_%2$sout_bits_pos,-1,* ', $link['tag'], $v6_el);
            }
        }

        /* generate graph area/stack for inbound */
        $i = 0;
        foreach ($knownlinks as $link) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive'] && ConfigApplication::getAsStatsConfigGraph()['brighten_negative']) {
                $col = \sprintf('%sBB', $link['color']);
            } else {
                $col = $link['color'];
            }

            $descr = \str_replace(':', '\:', $link['descr']); # Escaping colons in description
            $cmd .= \sprintf('AREA:%s_%sin_bits#%s:"%s"', $link['tag'], $v6_el, $col, $descr);

            if ($i > 0) {
                $cmd .= ':STACK';
            }

            $cmd .= ' ';
            $i++;
        }

        /* generate graph area/stack for outbound */
        $i = 0;
        foreach ($knownlinks as $link) {
            if (ConfigApplication::getAsStatsConfigGraph()['outispositive'] || !ConfigApplication::getAsStatsConfigGraph()['brighten_negative']) {
                $col = $link['color'];
            } else {
                $col = \sprintf('%sBB', $link['color']);
            }

            $cmd .= \sprintf('AREA:%s_%sout_bits#%s:', $link['tag'], $v6_el, $col);

            if ($i > 0) {
                $cmd .= ':STACK';
            }

            $cmd .= ' ';
            $i++;
        }

        $cmd .= 'COMMENT:\' \\n\' ';

        if (ConfigApplication::getAsStatsConfigGraph()['show95th']) {
            $cmd .= 'LINE1:tot_in_bits_95th#FF0000 ';
            $cmd .= 'LINE1:tot_out_bits_95th#FF0000 ';
            $cmd .= 'GPRINT:tot_in_bits_95th_pos:\'95th in %6.2lf%s\' ';
            $cmd .= 'GPRINT:tot_out_bits_95th_pos:\'/ 95th out %6.2lf%s\\n\' ';
        }

        # zero line
        $cmd .= 'HRULE:0#00000080';

        $response = new Response();
        $response->headers->set('Content-type', 'image/png');
        $response->sendHeaders();
        $response->setContent(\sprintf('%s', passthru($cmd)));

        return $response;
    }
}
