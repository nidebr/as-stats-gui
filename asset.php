<?php include("func.inc"); ?>

<?php
$val_searchasset = isset($_GET['asset']) ? $_GET['asset'] : "";
$aff_customlinks = $aff_otheras = $aff_toolsbox_add = "";

if ( isset($_GET['asset']) ) {
  $asset = strtoupper($_GET['asset']);
} else {
  $asset = "";
}

if ( isset($_GET['action']) ) {
  $action = $_GET['action'];
  if ( $action == "clearall" ) {
  	clearCacheFileASSET("all");
  	header("Location: asset.php");
  } else if ( $action == "clear" and $asset ) {
  	clearCacheFileASSET($asset);
  	header("Location: asset.php?asset=".$asset."");
  }
}

if ( $asset ) {
  if(!isset($peerusage)) $peerusage = 0;
  $hours = 24;
  $start = time() - $hours*3600;
  $end = time();

  $title = "AS-Stats | History for AS-SET: ".$asset;
  $header = 'History for AS-SET';
  $header_small = $asset;
  $select_form = "";

  $aslist = getASSET($asset);

  if ($aslist[0]) {
  	foreach( $aslist as $as ) {
  		$as_tmp = substr($as, 2);
  		if (is_numeric($as_tmp)) {
  			$as_num[]=$as_tmp;
  		} else {
  			$as_other[]=$as;
  		}
    }
  }

  $i = 0;
  $aff_astable = '<ul class="nav nav-stacked">';

  if (isset($as_other[0])) {
    $aff_otheras .= '<ul class="nav nav-stacked">';
    foreach( $as_other as $as ) {
      $aff_otheras .= '<li class="li-customlinks">';
      $aff_otheras .= '<a href="asset.php?asset='.$as.'">' . $as . '</a>';
      $aff_otheras .= "</li>";
    }
  $aff_otheras .= '</li>';
  }

  foreach( $as_num as $as ) {
    $asinfo = getASInfo($as);
    $class = (($i % 2) == 0) ? "" : "even";

    $aff_astable .= '<li class="li-padding '. $class .'">';

    // FLAGS
    if ( isset($asinfo['country']) ) $flagfile = "flags/" . strtolower($asinfo['country']) . ".gif";
    if (file_exists($flagfile)) {
      $is = getimagesize($flagfile);
      $img_flag = '<img src="'.$flagfile.'" '.$is[3].'>';
    }

    $aff_astable .= '<div class="row">';

    $aff_astable .= '<div class="col-lg-2">';
    $aff_astable .= '<b>' . $img_flag . ' AS' . $as . ': </b><small><i>' . $asinfo['descr'] . '</i></small>';

    // CUSTOM LINKS
    $htmllinks = array();
    foreach ($customlinks as $linkname => $url) {
    	$url = str_replace("%as%", $as, $url);
    	$htmllinks[] = "<a href=\"$url\" target=\"_blank\">" . htmlspecialchars($linkname) . "</a>\n";
    }
    $aff_astable .= '<div class="small">' . join(" | ", $htmllinks) . '</div>';

    // RANK
    $aff_astable .= '<div class="rank">';
  	$aff_astable .= '#' . ($i+1);
  	$aff_astable .= '</div>';

    $aff_astable .= '</div>';

    $img_v4 = getHTMLUrl($as, 4, $asinfo['descr'], $start, $end, $peerusage);

    if ( !$img_v4 ) $img_v4 = "No data found for AS".$as;
    if ($showv6) $img_v6 = getHTMLUrl($as, 6, $asinfo['descr'], $start, $end, $peerusage);

    if ($showv6) { $col = "5"; } else { $col="10"; }
    $aff_astable .= '<div class="col-lg-'.$col.'">';
    $aff_astable .= '<span class="pull-right">';
    $aff_astable .= $img_v4;
    $aff_astable .= '</span>';
    $aff_astable .= '</div>';

    if ($showv6) {
      $aff_astable .= '<div class="col-lg-5">';
      $aff_astable .= '<span class="pull-right">';
      $aff_astable .= $img_v6;
      $aff_astable .= '</span>';
      $aff_astable .= '</div>';
    }

    $aff_astable .= '</div>';

    $aff_astable .= '</li>';

    $i++;
  }
  $aff_astable .= '</ul>';

  // TOOLSBOX
  $aff_toolsbox_add = '<a href="asset.php?asset='.$asset.'&action=clear" class="list-group-item"><i class="fa fa-remove text-red"></i> Remove AS-SET cache file for '.$asset.'.</a>';
} else {
  $title = "AS-Stats | View AS-SET";
  $header = 'History for AS-SET';
  $header_small = "";

  $select_form = 'onload="document.forms[0].asset.focus(); document.forms[0].asset.select();"';
}

// TOOLSBOX
$aff_toolsbox = '<div class="list-group list-group-unbordered">';
$aff_toolsbox .= '<a href="asset.php?action=clearall" class="list-group-item"><i class="fa fa-remove text-red"></i> Remove all AS-SET cache files.</a>';
$aff_toolsbox .= $aff_toolsbox_add;
$aff_toolsbox .= '</div>';

?>

<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $title ?></title>
  <link rel="icon" href="favicon.ico" />
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/font-awesome/font-awesome.min.css">
  <link rel="stylesheet" href="plugins/ionicons/ionicons.min.css">
  <link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css">
  <link rel="stylesheet" href="plugins/morris/morris.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="css/custom.css">
</head>
<body class="hold-transition skin-black-light sidebar-collapse layout-top-nav" <?php echo $select_form; ?>>

<div class="wrapper">

  <!-- =============================================== -->
  <?php echo menu(); ?>
  <!-- =============================================== -->

  <div class="content-wrapper">
    <?php echo content_header($header, $header_small); ?>

    <section class="content">
      <div class="row">

        <div class="col-lg-2">

          <div class="row">
            <div class="col-lg-12">

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Search AS-SET</h3>
                </div>
                <div class="box-body">
                  <form class="navbar-form navbar-left" role="search">
                  <div class="input-group">
                  <input type="text" class="form-control menu-input" name="asset" placeholder="Search AS-SET" value="<?php echo $val_searchasset; ?>">
                  <span class="input-group-btn">
                  <button type="submit" class="btn btn-flat button-input"><i class="fa fa-search"></i></button>
                  </span>
                  </div>
                  </form>
                </div>
              </div>

            </div>
          </div>

          <div class="row">
            <div class="col-lg-12">

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Tools Box</h3>
                </div>
                <div class="box-body">
                  <?php echo $aff_toolsbox; ?>
                </div>
              </div>

            </div>
          </div>

          <?php if ( $aff_otheras ) { ?>
          <div class="row">
            <div class="col-lg-12">

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Other AS-SET</h3>
                </div>
                <div class="box-body">
                  <?php echo $aff_otheras; ?>
                </div>
              </div>

            </div>
          </div>
          <?php } ?>

        </div>

        <?php if ( isset($_GET['asset'])) { ?>
        <div class="col-lg-10">
          <div class="row">
            <div class="col-lg-12">

              <div class="box box-primary">
                <div class="box-body">
                  <?php echo $aff_astable; ?>
                </div>
              </div>

            </div>
          </div>
        </div>
        <?php } ?>

      </div>
    </section>

  </div>

  <!-- =============================================== -->
  <?php echo footer(); ?>
  <!-- =============================================== -->

</div>

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="plugins/fastclick/fastclick.js"></script>
<script src="dist/js/app.min.js"></script>

</body>
</html>
