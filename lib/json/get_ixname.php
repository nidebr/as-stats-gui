<?php
require_once("../class/peeringdb.php");

$peer = new PeeringDB();
$return = NULL;

if ( isset($_GET['name']) ) {
  foreach ($peer->GetIXName($_GET['name']) as $key => $value) {
    $return[] = array (
        'id' => strval($value->id),
        'name' => strval($value->name) . " (".$value->city." / ".$value->country.")",
    );
  }

  print_r(json_encode($return));
}
?>
