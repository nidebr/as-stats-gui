<?php

namespace Models;
use Controllers\Func;

class SqlLite
{
  protected $db;

  public function __construct($app)
  {
      $this->db = $app;
  }

  public function GetASStatsTop($ntop, $statsfile, $selected_links, $list_asn = NULL)
  {
    if(sizeof($selected_links) == 0){
  		$selected_links = array();
  		foreach(Func::getKnowlinks() as $link)
  			$selected_links[] = $link['tag'];
  	}

    $nlinks = 0;
	  $query_total = '0';
	  $query_links = '';

    foreach($selected_links as $tag){
  		$query_links .= "${tag}_in, ${tag}_out, ${tag}_v6_in, ${tag}_v6_out, ";
  		$nlinks += 4;
  		$query_total .= " + ${tag}_in + ${tag}_out + ${tag}_v6_in + ${tag}_v6_out";
    }

    if ( $list_asn ) {
      $where = implode(",", $list_asn);
      $query = "SELECT asn, $query_links $query_total as total FROM stats WHERE asn IN ( $where ) ORDER BY total DESC limit $ntop";
    } else {
      $query = "SELECT asn, $query_links $query_total as total FROM stats ORDER BY total DESC limit $ntop";
    }

    $asn =  $this->db[$statsfile]->fetchAll($query);

    $asstats = array();
    foreach ($asn as $key => $row) {
      $tot_in = 0;
  		$tot_out = 0;
  		$tot_v6_in = 0;
  		$tot_v6_out = 0;

      foreach($row as $key => $value){
        if (strpos($key, '_in') !== false) {
				if (strpos($key, '_v6_') !== false)
					$tot_v6_in += $value;
				else
					$tot_in += $value;
  			} else if (strpos($key, '_out') !== false) {
  				if (strpos($key, '_v6_') !== false)
  					$tot_v6_out += $value;
  				else
  					$tot_out += $value;
  			}
      }

      $asstats[$row['asn']] = array($tot_in, $tot_out, $tot_v6_in, $tot_v6_out);
    }
    return $asstats;
  }
}
