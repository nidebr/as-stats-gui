<?php

namespace Controllers;
use Application\ConfigApplication as ConfigApplication;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class Func
{
  protected $app;

  public function __construct($app)
  {
    $this->app = $app;
  }

  public function getKnowlinks()
  {
    $knownlinksfile = ConfigApplication::getKnowlinksFile();

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

  public function getASInfo($asnum)
  {
    $asinfodb = self::readasinfodb();

    if (@$asinfodb[$asnum])
		  return $asinfodb[$asnum];
  	else
  		return array('name' => "AS$asnum", 'descr' => "AS $asnum", 'country' => 'US');
  }

  private function readasinfodb()
  {
    if (!file_exists(ConfigApplication::getASInfoFile()))
		  return array();

    $fd = fopen(ConfigApplication::getASInfoFile(), "r");

    if ( !$fd )
      return array();

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

  public function format_bytes($bytes)
  {
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

  public function getCustomLinks($as)
  {
    $htmllinks = array();
    foreach(ConfigApplication::getCustomLinks() as $linkname => $url )
    {
      $url = str_replace("%as%", $as, $url);
  	  $htmllinks[] = "<a href=\"$url\" target=\"_blank\">" . htmlspecialchars($linkname) . "</a>\n";
    }

    return join(" | ", $htmllinks);
  }

  public function getCustomLinks_History($as)
  {
    if ( ConfigApplication::getCustomLinks() ) {
      $aff_customlinks = '<div class="list-group list-group-unbordered">';
      foreach (ConfigApplication::getCustomLinks() as $linkname => $url) {
        $url = str_replace("%as%", $as, $url);
        $aff_customlinks .= '<a href='.$url.' target="_blank" class="list-group-item"><i class="fa fa-external-link text-blue"></i> ' . htmlspecialchars($linkname) . '</a>';
      }
      $aff_customlinks .= "</div>";

      return $aff_customlinks;
    } else { return FALSE; }
  }

  public function getRouteName(Request $request)
  {
    $dpagename = $request->get('_route');
    $active_top = $dpagename == "index" ? "active": "";
	  $active_searchas = $dpagename == "history" ? "active" : "";
	  $active_searchasset = $dpagename == "asset" ? "active" : "";
    $active_ix = $dpagename == "ixstats" ? "active" : "";
    $active_linkusage = $dpagename == "linkusage" ? "active" : "";

    $return = [
      'active_top' => $active_top,
      'active_searchas' => $active_searchas,
      'active_searchasset' => $active_searchasset,
      'active_ix' => $active_ix,
      'active_linkusage' => $active_linkusage,
    ];

    return $return;
  }

  public function getRRDFileForAS($as, $peer = 0)
  {
    $rrdpath = ConfigApplication::getASStatsAllConfig()['rrdpath'];
    $prefix = ($peer == 1) ? "$rrdpath/peeras" : "$rrdpath";
	  return "$prefix/" . sprintf("%02x", $as % 256) . "/$as.rrd";
  }

  public function statsFileForHours($hours)
  {
    $top = ConfigApplication::getTopInterval();
    if ( $top ) {
    	foreach ($top as $key => $interval) {
    		if ($interval['hours'] == $hours) {
    			return $key."statsfile";
    		}
    	}
    }
  	return "daystatsfile";
  }

  public function statsLabelForHours($hours)
  {
    $top = ConfigApplication::getTopInterval();

    if ( $top ) {
    	foreach ($top as $key => $interval) {
    		if ($interval['hours'] == $hours) {
          $label = explode(' ', $interval['label']);
    			return $label[0] . " " . $this->app['translator']->trans($label[1]);
    		}
    	}
    }
    return (int)$hours . " " . $this->app['translator']->trans("hours");
  }
}
