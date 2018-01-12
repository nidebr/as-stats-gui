<?php

namespace Models;
use Controllers\Func;
use Application\ConfigApplication as ConfigApplication;

class Whois
{
  protected $app;

  public function __construct($app)
  {
    $this->app = $app;
  }

  public function getASSET($asset)
  {
    $aslist = NULL;
    $cache = FALSE;

    /* sanity check */
	  if (!preg_match("/^[a-zA-Z0-9:_-]+$/", $asset)) return NULL;

    $assetfile = ConfigApplication::getASSetPath()."/".$asset.".txt";

    if ( file_exists($assetfile) ) {
      $filemtime = @filemtime($assetfile);

      if (!$filemtime or (time() - $filemtime >= ConfigApplication::getASSetCacheLife())) {
        $aslist = self::getWhois($asset, $assetfile);
      } else {
        $f = fopen($assetfile, "r");
    	  $aslist = array();
        while (!feof($f)) {
    	    $line = trim(fgets($f));
    	    if (!empty($line)) $aslist[] = $line;
        }
        $cache = TRUE;
      }
    } else {
      $aslist = self::getWhois($asset, $assetfile);
    }

    $parse_aslist = self::parseOtherAsset($aslist);

    return array('cache' => $cache, 'aslist' => $parse_aslist['aslist'], 'other_asset' => $parse_aslist['other_asset']);
  }

  private function getWhois($asset, $assetfile)
  {
    $cmd = ConfigApplication::getWhois() ." -h whois.radb.net '!i".$asset."'";

    $return_aslist = explode("\n",shell_exec($cmd));

    /* find the line that contains the AS-SET members */
		$aslist = NULL;
		foreach ($return_aslist as $asline) {
			if (preg_match("/^AS/", $asline)) {
				$aslist = explode(" ", $asline);
				break;
			}
		}

    if ( $aslist ) {
      $f = fopen($assetfile,"w");
      foreach ($aslist as $as) {
      	fputs($f,$as."\n");
      }
      fclose($f);
    }

    return $aslist;
  }

  private function parseOtherAsset($aslist)
  {
    $as_num = $as_other = NULL;

    if ($aslist) {
    	foreach( $aslist as $as ) {
    		$as_tmp = substr($as, 2);
    		if (is_numeric($as_tmp)) {
    			$as_num[]=$as_tmp;
    		} else {
    			$as_other[]=$as;
    		}
      }
    }

    return array('aslist' => $as_num, 'other_asset' => $as_other);
  }
}
