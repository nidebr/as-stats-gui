<?php
if ( file_exists('../../config.inc') ) {
  require_once '../../config.inc';
} elseif ( file_exists('config.inc') ) {
  require_once 'config.inc';
}

class PeeringDB {
  protected $url = NULL;

  public function __construct() {
    global $peeringdb;
    $this->url = 'https://peeringdb.com/api';
  }

  protected function sendRequest( $url ) {
    $ch = curl_init();
  	curl_setopt($ch, CURLOPT_URL, $url);
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  	$output = curl_exec($ch);
  	curl_close($ch);

  	return $output;
  }

  public function GetInfo( $asn = NULL ) {
    if ( $asn ) {
      $json = $this->sendRequest($this->url."/net?asn=".$asn);
		  $json = json_decode($json);
      if ( isset($json->meta->error) ) { return 0; }
      else { return $json->data[0]; }
    }
  }

  public function GetIX( $asn = NULL ) {
    if ( $asn ) {
      $json = $this->sendRequest($this->url."/netixlan?asn=".$asn);
		  $json = json_decode($json);

      if ( empty($json->data) ) { return 0; }
      else { return $json->data; }
    }
  }

  public function GetAllNet( $regex = NULL) {
    if ( $regex ) { $regex = '?asn__contains='.$regex; }
    $json = json_decode($this->sendRequest($this->url."/net".$regex));
    return $json->data;
  }

  public function GetIXInfo( $id = NULL ) {
    if ( $id ) {
      $json = $this->sendRequest($this->url."/ix?id=".$id);
		  $json = json_decode($json);

      if ( empty($json->data) ) { return 0; }
      else { return $json->data; }
    }
  }

  public function GetIXMembers( $id = NULL ) {
    if ( $id ) {
      $json = $this->sendRequest($this->url."/net?ix_id=".$id);
		  $json = json_decode($json);

      if ( empty($json->data) ) { return 0; }
      else { return $json->data; }
    }
  }

  public function GetIXMembersLan( $id = NULL ) {
    if ( $id ) {
      $json = $this->sendRequest($this->url."/netixlan?ix_id=".$id);
		  $json = json_decode($json);

      if ( empty($json->data) ) { return 0; }
      else { return $json->data; }
    }
  }

  public function GetIXASN($id = NULL) {
    $return = array();
    if ( $id ) {
      foreach ($this->GetIXMembers($id) as $key => $value) {
        $return[] = $value->asn;
      };
    }

    return $return;
  }

  public function GetIXName( $regex = NULL) {
    if (is_string($regex)) { $regex = '?name__contains='.urlencode($regex); }
    else $regex = '';
    $json = json_decode($this->sendRequest($this->url."/ix".$regex));
    return $json->data;
  }
}
?>
