<?php
/*
 * $Id$
 *
 * written by Manuel Kasper <mk@neon1.net> for Monzoon Networks AG
 */

require_once('func.inc');

$numtop = 10;
$ascolors = array("A6CEE3", "1F78B4", "B2DF8A", "33A02C", "FB9A99", "E31A1C", "FDBF6F", "FF7F00", "CAB2D6", "6A3D9A");

$link = $_GET['link'];
if (!preg_match("/^[0-9a-zA-Z][0-9a-zA-Z\-_]+$/", $link))
	die("Invalid link");

$v6_el = "";
if (@$_GET['v'] == 6)
	$v6_el = "v6_";

$hours = 24;
if (@$_GET['numhours'])
	$hours = (int)$_GET['numhours'];

$statsfile = statsFileForHours($hours);
if (@$_GET['v'] == 6) {
	$topas = getasstats_top($numtop, $statsfile, array($_GET['link']), $list_asn = NULL, $v=6);
}else {
	$topas = getasstats_top($numtop, $statsfile, array($_GET['link']));
}

/* now make a beautiful graph :) */
header("Content-Type: image/png");

$width = $default_graph_width;
$height = $default_graph_height;
if (@$_GET['width'])
	$width = (int)$_GET['width'];
if (@$_GET['height'])
	$height = (int)$_GET['height'];

$knownlinks = getknownlinks();

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

if($showtitledetail && @$_GET['dname'] != "")
	$cmd .= "--title " . escapeshellarg($_GET['dname']) . " ";
else
	if (isset($_GET['v']) && is_numeric($_GET['v']))
		$cmd .= "--title IPv" . $_GET['v'] . " ";

/* geneate RRD DEFs */
foreach ($topas as $as => $traffic) {
	$rrdfile = getRRDFileForAS($as);
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
	$asinfo = getASInfo($as);
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

passthru($cmd);

exit;

?>
