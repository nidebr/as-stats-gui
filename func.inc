<?php
require_once("config_defaults.inc");
require_once('config.inc');
require_once('plugins/mobile-detect/Mobile_Detect.php');

/* make sure we have enough memory, as some pages can be quite memory intensive */
ini_set("memory_limit", "256M");
/* note: you might want to put the data from asinfo.txt into an SQL
   database to avoid having to read the whole file all the time */
function getASInfo($asnum) {
	global $asinfodb;

	if (!isset($asinfodb))
		$asinfodb = readasinfodb();

	if (@$asinfodb[$asnum])
		return $asinfodb[$asnum];
	else
		return array('name' => "AS$asnum", 'descr' => "AS $asnum");
}

function readasinfodb() {
	global $asinfofile;

	if (!file_exists($asinfofile))
		return array();

	$fd = fopen($asinfofile, "r");
	$asinfodb = array();
	while (!feof($fd)) {
		$line = trim(fgets($fd));
		if (preg_match("/(^\\s*#)|(^\\s*$)/", $line))
			continue;	/* empty line or comment */

		$asnarr = explode("\t", $line);
		$asn = $asnarr[0];
		$asname = $asnarr[1];
		$descr = $asnarr[2];
		if (isset($asnarr[3])) $country = $asnarr[3];

		$asinfodb[$asn] = array(
			'name' => $asname,
			'descr' => $descr,
			'country' => $country
		);
	}
	fclose($fd);

	return $asinfodb;
}

function getknownlinks() {
	global $knownlinksfile;
	$fd = fopen($knownlinksfile, "r");
	$knownlinks = array();
	while (!feof($fd)) {
		$line = trim(fgets($fd));
		if (preg_match("/(^\\s*#)|(^\\s*$)/", $line))
			continue;	/* empty line or comment */

		list($routerip,$ifindex,$tag,$descr,$color) = preg_split("/\\t+/", $line);
		$known = false;
		foreach ($knownlinks as $link) {
		    if (in_array($tag,$link)) {$known=true;}
		}
		if (!$known) {
		    $knownlinks[] = array(
			'routerip' => $routerip,
			'ifindex' => $ifindex,
			'tag' => $tag,
			'descr' => $descr,
			'color' => $color
		    );
		}
	}
	fclose($fd);

	return $knownlinks;
}

function getasstats_top($ntop, $statfile) {
	/* first step: walk the data for all ASes to determine the top 5 for the given link */
	$fd = fopen($statfile, "r");
	if (!$fd)
		return array();
	$cols = explode("\t", trim(fgets($fd)));

	/* read in up to $ntop AS stats, sum up columns */
	while (!feof($fd)) {
		$line = trim(fgets($fd));
		if (!$line)
			continue;

		$els = explode("\t", $line);

		/* first element is the AS */
		$as = $els[0];
		$tot_in = 0;
		$tot_out = 0;
		$tot_v6_in = 0;
		$tot_v6_out = 0;

		for ($i = 1; $i < count($els); $i++) {
			if (strpos($cols[$i], "_in") !== false) {
				if (strpos($cols[$i], "_v6_") !== false)
					$tot_v6_in += $els[$i];
				else
					$tot_in += $els[$i];
			} else {
				if (strpos($cols[$i], "_v6_") !== false)
					$tot_v6_out += $els[$i];
				else
					$tot_out += $els[$i];
			}
		}

		$asstats[$as] = array($tot_in, $tot_out, $tot_v6_in, $tot_v6_out);

		if (count($asstats) >= $ntop)
			break;
	}
	fclose($fd);

	return $asstats;
}

function format_bytes($bytes) {
	if ($bytes >= 1099511627776)
		return sprintf("%.2f TB", $bytes / 1099511627776);
	else if ($bytes >= 1073741824)
		return sprintf("%.2f GB", $bytes / 1073741824);
	else if ($bytes >= 1048576)
		return sprintf("%.2f MB", $bytes / 1048576);
	else if ($bytes >= 1024)
		return sprintf("%d KB", $bytes / 1024);
	else
		return "$bytes bytes";
}

function getRRDFileForAS($as, $peer = 0) {
	global $rrdpath;
	$prefix = ($peer == 1) ? "$rrdpath/peeras" : "$rrdpath";
	return "$prefix/" . sprintf("%02x", $as % 256) . "/$as.rrd";
}

function getASSET($asset) {
	global $whois, $assetpath, $asset_cache_life;

	/* sanity check */
	if (!preg_match("/^[a-zA-Z0-9:_-]+$/", $asset)) return null;

	$assetfile = $assetpath."/".$asset.".txt";
	# check if file exist and cache
	$filemtime = @filemtime($assetfile);
	if (!$filemtime or (time() - $filemtime >= $asset_cache_life)) {
  	$cmd = $whois ." -h whois.radb.net '!i".$asset."'";
    $return_aslist = explode("\n",shell_exec($cmd));

		/* find the line that contains the AS-SET members */
		$aslist = array();
		foreach ($return_aslist as $asline) {
			if (preg_match("/^AS/", $asline)) {
				$aslist = explode(" ", $asline);
				break;
			}
		}

    $f = fopen($assetfile,"w");
    foreach ($aslist as $as) {
    	fputs($f,$as."\n");
    }
    fclose($f);
	# else read cache file
	} else {
		$f = fopen($assetfile, "r");
    	$aslist = array();
		while (!feof($f)) {
			$line = trim(fgets($f));
			if (!empty($line))
				$aslist[] = $line;
		}
	}
	return $aslist;
}

function clearCacheFileASSET($asset) {
	global $assetpath;

	/* sanity check */
	if (!preg_match("/^[a-zA-Z0-9:_-]+$/", $asset))
		return;
	if ( $asset == "all" ) {
		$files = glob($assetpath."/*.txt");
		foreach($files as $file) {
			unlink($file);
		}
	} else {
		$file = $assetpath."/".$asset.".txt";
		unlink($file);
	}
}

# return the html used in top.php : <a href=blabla><img src=blabla/></url>
function getHTMLUrl($as, $ipversion, $desc, $start, $end, $peerusage){
	$result = "";

	$img = getHTMLImg($as, $ipversion, $desc, $start, $end, $peerusage, '', '', false);

	if ( $img ) {
		$result = "<a href='history.php?as=$as&peerusage=$peerusage&v=$ipversion'>$img</a>";
	}

	return($result);
}

# return the html used in history.php (for example) : <img src=blabla/>
function getHTMLImg($as, $ipversion, $desc, $start, $end, $peerusage, $alt, $class, $history = false){
	global $top_graph_width;
	global $top_graph_height;
	$result = "";

	$rrdfile = getRRDFileForAS($as);
	if (file_exists($rrdfile)) {
		$dname = rawurlencode("AS$as - $desc - IPV$ipversion");
		$result = "<img alt='$alt' class='$class' src='gengraph.php?v=$ipversion&as=$as&peerusage=$peerusage&dname=$dname&start=$start&end=$end";
		if(!$history)
			$result .= "&width=$top_graph_width&height=$top_graph_height&nolegend=1";
		$result .= "'";
		if(!$history)
			$result .= " width='$top_graph_width' height='$top_graph_height' border='0'";
		$result .= "/>";
	}
	return($result);
}

function statsFileForHours($hours) {
	global $top_intervals, $daystatsfile;
	foreach ($top_intervals as $interval) {
		if ($interval['hours'] == $hours && @$interval['statsfile']) {
			return $interval['statsfile'];
		}
	}
	return $daystatsfile;
}

function statsLabelForHours($hours) {
	global $top_intervals;
	foreach ($top_intervals as $interval) {
		if ($interval['hours'] == $hours && @$interval['label']) {
			return $interval['label'];
		}
	}
	return (int)$hours . " hours";
}

function debug($txt) {
  print "<pre>";
  print_r($txt);
  print "</pre>";
}

function menu() {
	global $top_intervals;

	//echo count($top_intervals);

	$dpagename = basename($_SERVER['PHP_SELF'], ".php");
	$active_top = $dpagename == "index" ? "active": "";
	$active_searchas = $dpagename == "history" ? 'class="active"' : "";
	$active_searchasset = $dpagename == "asset" ? 'class="active"' : "";
	$active_linkusage = $dpagename == "linkusage" ? "active" : "";

  $return = '<header class="main-header">';
  $return .= '<nav class="navbar navbar-static-top">';
  $return .= '<div class="container pull-left">';

  $return .='<div class="navbar-header">';
  $return .='<a href="index.php" class="navbar-brand"><b>AS-Stats</b></a>';
  $return .='<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">';
  $return .='<i class="fa fa-bars"></i>';
  $return .='</button>';
  $return .='</div>';

  $return .='<div class="collapse navbar-collapse pull-left" id="navbar-collapse">';
  $return .='<ul class="nav navbar-nav">';

  if ( count($top_intervals) < 2 ) {
		$return .= '<li class="'.$active_top.'"><a href="index.php">Top AS</a></li>';
	} else {
		$return .= '<li class="dropdown '.$active_top.'">';
		$return .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Top AS <span class="caret"></span></a>';
		$return .= '<ul class="dropdown-menu" role="menu">';
		foreach ($top_intervals as $interval) {
			$return .= '<li><a href="index.php?numhours=' . $interval['hours'] . '">Top AS - ' . $interval['label'] . '</a></li>';
		}
		$return .= '</ul>';
		$return .= '</li>';
	}

	$return .='<li '.$active_searchas.'><a href="history.php">View AS</a></li>';
	$return .='<li '.$active_searchasset.'><a href="asset.php">View AS-SET</a></li>';

	if ( count($top_intervals) < 2 ) {
  	$return .='<li class="'.$active_linkusage.'"><a href="linkusage.php">Link Usage</a></li>';
	} else {
		$return .= '<li class="dropdown '.$active_linkusage.'">';
		$return .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Link Usage <span class="caret"></span></a>';
		$return .= '<ul class="dropdown-menu" role="menu">';
		foreach ($top_intervals as $interval) {
			$return .= '<li><a href="linkusage.php?numhours=' . $interval['hours'] . '">Link Usage - ' . $interval['label'] . '</a></li>';
		}
		$return .= '</ul>';
		$return .= '</li>';
	}

  $return .='</ul>';

	$val_ntop = isset($_GET['n']) ? $_GET['n'] : "";

	if ($dpagename == "index") {
		$return .='<form class="navbar-form navbar-left" role="search">';
	  $return .='<div class="input-group">';
	  $return .='<input type="text" class="form-control menu-input" name="n" placeholder="Number of AS" value="'.$val_ntop.'">';
		$return .='<span class="input-group-btn">';
	  $return .='<button type="submit" class="btn btn-flat button-input"><i class="fa fa-search"></i></button>';
	  $return .='</span>';
	  $return .='</div>';
	  $return .='</form>';
	}

  $return .='</div>';
  $return .='</div>';
  $return .='</nav>';
  $return .='</header>';

  return $return;
}

function footer() {
  $footer = '<footer class="main-footer font-default">';
  $footer .= '<div class="pull-right hidden-xs">';
  $footer .= '<b>GUI Version</b> 0.1';
  $footer .= '</div>';
  $footer .= '<strong>AS-Stats v1.6</strong> written by Manuel Kasper for Monzoon Networks AG.';

  $footer .= '</footer>';

  return $footer;
}

function content_header($titre, $small) {
	global $outispositive;

  $header = '<section class="content-header">';
  $header .= '<h1>';
  $header .= $titre;
  $header .= '<small><i>'. $small .'</i></small>';
  $header .= '</h1>';

	if ($outispositive) {
		$txt_traffic = '<i class="fa fa-arrow-up"></i> Outbound traffic <i class="fa fa-arrow-down"></i> Inbound traffic';
	} else {
		$txt_traffic = '<i class="fa fa-arrow-up"></i> Inbound traffic <i class="fa fa-arrow-down"></i> Outbound traffic';
	}

	$header .= '<ol class="breadcrumb">';
	$header .= '<li> '. $txt_traffic .' </li>';
	$header .= '</ol>';

  $header .= '</section>';

	$header .= '<hr>';

  return $header;
}

function box_linkusage ($title, $img) {
	$return = '<div class="box box-primary">';
	$return .= '<div class="box-header with-border">';
	$return .= '<i class="fa fa-bar-chart-o"></i>';
	$return .= '<h3 class="box-title">'.$title.'</h3>';
	$return .= '</div>';
	$return .= '<div class="box-body">';
	$return .= '<center>'.$img.'</center>';
	$return .= '</div>';
	$return .= '</div>';

	return $return;
}
?>
