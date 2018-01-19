<?php

namespace Controllers;
use Silex\Application;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Application\ConfigApplication as ConfigApplication;

/**
 * @SLX\Controller(prefix="/render")
 */
class Render
{
  public function __construct()
  {
    $this->params = ConfigApplication::getASStatsAllConfig();
  }

  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri=""),
     *      @SLX\Bind(routeName="render")
     * )
  */
  public function index(Request $request, Application $app)
  {
    $outispositive = $this->params['outispositive'];
    $showtitledetail = $this->params['showtitledetail'];
    $vertical_label = $this->params['vertical_label'];
    $brighten_negative = $this->params['brighten_negative'];
    $compat_rrdtool12 = $this->params['compat_rrdtool12'];
    $show95th = $this->params['show95th'];
    $rrdtool = $this->params['rrdtool'];

    $req = $request->query->all();

    if (!preg_match("/^[0-9a-zA-Z]+$/", $req['as'])) die("Invalid AS");

    $width = $this->params['default_graph_width'];
    $height = $this->params['default_graph_height'];

    if (isset($req['width'])) $width = (int)$req['width'];

    if (isset($req['height'])) $height = (int)$req['height'];

    $v6_el = "";
    if (@$req['v'] == 6) $v6_el = "v6_";

    if(isset($req['peerusage']) && $req['peerusage'] == '1') {
    	$peerusage = 1;
    } else {
    	$peerusage = 0;
    }

    $knownlinks = $app['func']->getKnowlinks();

    if(isset($req['selected_links']) && !empty($req['selected_links'])) {
    	$reverse = array();

    	foreach($knownlinks as $link) {
    		$reverse[$link['tag']] = array('color' => $link['color'], 'descr' => $link['descr']);
      }

    	$links = array();
    	foreach(explode(',', $req['selected_links']) as $tag) {
    		$link = array('tag' => $tag,
    				'color' => $reverse[$tag]['color'],
    				'descr' => $reverse[$tag]['descr']);
    		$links[] = $link;
    	}

    	$knownlinks = $links;
    }

    $rrdfile = $app['func']->getRRDFileForAS($req['as'], $peerusage);

    if ($compat_rrdtool12) {
    	/* cannot use full-size-mode - must estimate height/width */
    	$height -= 65;
    	$width -= 81;
    	if ($vertical_label)
    		$width -= 16;
    }

    $cmd = "$rrdtool graph - " .
    	"--slope-mode --alt-autoscale -u 0 -l 0 --imgformat=PNG --base=1000 --height=$height --width=$width " .
    	"--color BACK#ffffff00 --color SHADEA#ffffff00 --color SHADEB#ffffff00 ";

    if (!$compat_rrdtool12) $cmd .= "--full-size-mode ";

    if ($vertical_label) {
    	if($outispositive)
    		$cmd .= "--vertical-label '<- IN | OUT ->' ";
    	else
    		$cmd .= "--vertical-label '<- OUT | IN ->' ";
    }

    if($showtitledetail && @$req['dname'] != "")
    	$cmd .= "--title " . escapeshellarg($req['dname']) . " ";
    else
    	if (isset($req['v']) && is_numeric($req['v']))
    		$cmd .= "--title IPv" . $req['v'] . " ";

    if (isset($req['nolegend']) && $req['nolegend'] )
    	$cmd .= "--no-legend ";

    if (isset($req['start']) && is_numeric($req['start']))
    	$cmd .= "--start " . $req['start'] . " ";

    if (isset($req['end']) && is_numeric($req['end']))
    	$cmd .= "--end " . $req['end'] . " ";

    /* geneate RRD DEFs */
    foreach ($knownlinks as $link) {
    	$cmd .= "DEF:{$link['tag']}_{$v6_el}in=\"$rrdfile\":{$link['tag']}_{$v6_el}in:AVERAGE ";
    	$cmd .= "DEF:{$link['tag']}_{$v6_el}out=\"$rrdfile\":{$link['tag']}_{$v6_el}out:AVERAGE ";
    }

    if ($compat_rrdtool12) {
    	/* generate a CDEF for each DEF to multiply by 8 (bytes to bits), and reverse for outbound */
    	foreach ($knownlinks as $link) {
    	  if ($outispositive) {
    			$cmd .= "CDEF:{$link['tag']}_{$v6_el}in_bits={$link['tag']}_{$v6_el}in,-8,* ";
    			$cmd .= "CDEF:{$link['tag']}_{$v6_el}out_bits={$link['tag']}_{$v6_el}out,8,* ";
    		} else {
    			$cmd .= "CDEF:{$link['tag']}_{$v6_el}in_bits={$link['tag']}_{$v6_el}in,8,* ";
    			$cmd .= "CDEF:{$link['tag']}_{$v6_el}out_bits={$link['tag']}_{$v6_el}out,-8,* ";
    		}
    	}
    } else {
    	$tot_in_bits = "CDEF:tot_in_bits=0";
    	$tot_out_bits = "CDEF:tot_out_bits=0";

    	/* generate a CDEF for each DEF to multiply by 8 (bytes to bits), and reverse for outbound */
    	foreach ($knownlinks as $link) {
    		$cmd .= "CDEF:{$link['tag']}_{$v6_el}in_bits_pos={$link['tag']}_{$v6_el}in,8,* ";
    		$cmd .= "CDEF:{$link['tag']}_{$v6_el}out_bits_pos={$link['tag']}_{$v6_el}out,8,* ";
    		$tot_in_bits .= ",{$link['tag']}_{$v6_el}in_bits_pos,ADDNAN";
    		$tot_out_bits .= ",{$link['tag']}_{$v6_el}out_bits_pos,ADDNAN";
    	}

    	$cmd .= "$tot_in_bits ";
    	$cmd .= "$tot_out_bits ";

    	$cmd .= "VDEF:tot_in_bits_95th_pos=tot_in_bits,95,PERCENT ";
    	$cmd .= "VDEF:tot_out_bits_95th_pos=tot_out_bits,95,PERCENT ";

    	if ($outispositive) {
    		$cmd .= "CDEF:tot_in_bits_95th=tot_in_bits,POP,tot_in_bits_95th_pos,-1,* ";
    		$cmd .= "CDEF:tot_out_bits_95th=tot_out_bits,POP,tot_out_bits_95th_pos,1,* ";
    	} else {
    		$cmd .= "CDEF:tot_in_bits_95th=tot_in_bits,POP,tot_in_bits_95th_pos,1,* ";
    		$cmd .= "CDEF:tot_out_bits_95th=tot_out_bits,POP,tot_out_bits_95th_pos,-1,* ";
    	}

    	foreach ($knownlinks as $link) {
    		if ($outispositive) {
    			$cmd .= "CDEF:{$link['tag']}_{$v6_el}in_bits={$link['tag']}_{$v6_el}in_bits_pos,-1,* ";
    			$cmd .= "CDEF:{$link['tag']}_{$v6_el}out_bits={$link['tag']}_{$v6_el}out_bits_pos,1,* ";
    		} else {
    			$cmd .= "CDEF:{$link['tag']}_{$v6_el}out_bits={$link['tag']}_{$v6_el}out_bits_pos,-1,* ";
    			$cmd .= "CDEF:{$link['tag']}_{$v6_el}in_bits={$link['tag']}_{$v6_el}in_bits_pos,1,* ";
    		}
    	}
    }

    /* generate graph area/stack for inbound */
    $i = 0;

    foreach ($knownlinks as $link) {
    	if ($outispositive && $brighten_negative)
    		$col = $link['color'] . "BB";
    	else
    		$col = $link['color'];
    	$descr = str_replace(':', '\:', $link['descr']); # Escaping colons in description
    	$cmd .= "AREA:{$link['tag']}_{$v6_el}in_bits#{$col}:\"{$descr}\"";
    	if ($i > 0)
    		$cmd .= ":STACK";
    	$cmd .= " ";

    	$i++;
    }

    /* generate graph area/stack for outbound */
    $i = 0;
    foreach ($knownlinks as $link) {
    	if ($outispositive || !$brighten_negative)
    		$col = $link['color'];
    	else
    		$col = $link['color'] . "BB";
    	$cmd .= "AREA:{$link['tag']}_{$v6_el}out_bits#{$col}:";
    	if ($i > 0)
    		$cmd .= ":STACK";
    	$cmd .= " ";
    	$i++;
    }

    $cmd .= "COMMENT:' \\n' ";

    if ($show95th && !$compat_rrdtool12) {
    	$cmd .= "LINE1:tot_in_bits_95th#FF0000 ";
    	$cmd .= "LINE1:tot_out_bits_95th#FF0000 ";
    	$cmd .= "GPRINT:tot_in_bits_95th_pos:'95th in %6.2lf%s' ";
    	$cmd .= "GPRINT:tot_out_bits_95th_pos:'/ 95th out %6.2lf%s\\n' ";
    }

    # zero line
    $cmd .= "HRULE:0#00000080";
    $response = new Response();
    $response->headers->set('Content-type', 'image/png');
    $response->sendHeaders();
    $response->setContent(passthru($cmd));
    return $response;
  }

  /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="linkusage"),
     *      @SLX\Bind(routeName="render-linkusage")
     * )
  */
  public function linkusage(Request $request, Application $app)
  {
    $compat_rrdtool12 = $this->params['compat_rrdtool12'];
    $outispositive = $this->params['outispositive'];
    $showtitledetail = $this->params['showtitledetail'];
    $vertical_label = $this->params['vertical_label'];
    $brighten_negative = $this->params['brighten_negative'];

    $rrdtool = $this->params['rrdtool'];
    $req = $request->query->all();

    $numtop = 10;
    $ascolors = array("A6CEE3", "1F78B4", "B2DF8A", "33A02C", "FB9A99", "E31A1C", "FDBF6F", "FF7F00", "CAB2D6", "6A3D9A");

    $link = $_GET['link'];
    if (!preg_match("/^[0-9a-zA-Z][0-9a-zA-Z\-_]+$/", $link)) die("Invalid link");

    $v6_el = "";
    if (@$req['v'] == 6) $v6_el = "v6_";

    $hours = 24;
    if (@$req['numhours']) $hours = (int)$req['numhours'];

    $topas = $app['table.sql']->GetASStatsTop($numtop,$app['func']->statsFileForHours($hours), array($req['link']));

    $width = $this->params['default_graph_width'];
    $height = $this->params['default_graph_height'];

    if (@$req['width']) $width = (int)$req['width'];
    if (@$req['height']) $height = (int)$req['height'];

    $knownlinks = $app['func']->getKnowlinks();

    if ($compat_rrdtool12) {
    	/* cannot use full-size-mode - must estimate height/width */
    	$height -= 205;
    	$width -= 81;
    }

    $start = time() - $hours*3600;
    $end = time();

    $cmd = "$rrdtool graph - " .
    	"--slope-mode --alt-autoscale -u 0 -l 0 --imgformat=PNG --base=1000 --height=$height --width=$width " .
    	"--color BACK#ffffff00 --color SHADEA#ffffff00 --color SHADEB#ffffff00 " .
    	"--start " . $start . " --end " . $end . " ";

    if (!$compat_rrdtool12)
    	$cmd .= "--full-size-mode ";

    if ($vertical_label) {
    	if($outispositive)
    		$cmd .= "--vertical-label '<- IN | OUT ->' ";
    	else
    		$cmd .= "--vertical-label '<- OUT | IN ->' ";
    }

    if ( $showtitledetail && @$req['dname'] != "" ) {
      $cmd .= "--title " . escapeshellarg($req['dname']) . " ";
    } else {
    	if (isset($req['v']) && is_numeric($req['v'])) $cmd .= "--title IPv" . $req['v'] . " ";
    }

    /* geneate RRD DEFs */
    foreach ($topas as $as => $traffic) {
    	$rrdfile = $app['func']->getRRDFileForAS($as);
    	$cmd .= "DEF:as{$as}_{$v6_el}in=\"$rrdfile\":{$link}_{$v6_el}in:AVERAGE ";
    	$cmd .= "DEF:as{$as}_{$v6_el}out=\"$rrdfile\":{$link}_{$v6_el}out:AVERAGE ";
    }

    /* generate a CDEF for each DEF to multiply by 8 (bytes to bits), and reverse for outbound */
    foreach ($topas as $as => $traffic) {
    	if ($outispositive) {
    		$cmd .= "CDEF:as{$as}_{$v6_el}in_bits=as{$as}_{$v6_el}in,-8,* ";
    		$cmd .= "CDEF:as{$as}_{$v6_el}out_bits=as{$as}_{$v6_el}out,8,* ";
    	} else {
    		$cmd .= "CDEF:as{$as}_{$v6_el}in_bits=as{$as}_{$v6_el}in,8,* ";
    		$cmd .= "CDEF:as{$as}_{$v6_el}out_bits=as{$as}_{$v6_el}out,-8,* ";
    	}
    }

    /* generate graph area/stack for inbound */
    $i = 0;
    foreach ($topas as $as => $traffic) {
    	$asinfo = $app['func']->GetASInfo($as);
    	$descr = str_replace(":", "\\:", utf8_decode($asinfo['descr']));

    	$cmd .= "AREA:as{$as}_{$v6_el}in_bits#{$ascolors[$i]}:\"AS{$as} ({$descr})\\n\"";
    	if ($i > 0)
    		$cmd .= ":STACK";
    	$cmd .= " ";
    	$i++;
    }

    /* generate graph area/stack for outbound */
    $i = 0;
    foreach ($topas as $as => $traffic) {
    	$cmd .= "AREA:as{$as}_{$v6_el}out_bits#{$ascolors[$i]}:";
    	if ($i > 0)
    		$cmd .= ":STACK";
    	$cmd .= " ";
    	$i++;
    }

    # zero line
    $cmd .= "HRULE:0#00000080";

    $response = new Response();
    $response->headers->set('Content-type', 'image/png');
    $response->sendHeaders();
    $response->setContent(passthru($cmd));
    return $response;
  }
}
