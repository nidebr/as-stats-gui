<?php include("func.inc"); ?>

<?php
$selected_links = array();
$val_searchasset = isset($_GET['asset']) ? $_GET['asset'] : "";
$aff_customlinks = $aff_otheras = $aff_toolsbox_add = $aff_legend = "";

if(!isset($peerusage)) $peerusage = 0;

$hours = 24;
if (@$_GET['numhours']) $hours = (int)$_GET['numhours'];
if ($peerusage) {
  $statsfile = $daypeerstatsfile;
} else {
	$statsfile = statsFileForHours($hours);
}

$label = statsLabelForHours($hours);

$knownlinks = getknownlinks();
$selected_links = array();

// Mobile Detect for show legend
foreach($knownlinks as $link){
	if(isset($_GET["link_${link['tag']}"]))
		$selected_links[] = $link['tag'];
}

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

  if ( !empty($as_num)) {
    // LEGENDE
    $aff_legend .= "<table width=\"100%\" class='small'>";

    foreach ($knownlinks as $link) {
      $tag = "link_${link['tag']}";

      $checked = '';
      if(isset($_GET[$tag]) && $_GET[$tag] == 'on') {
        $checked = 'checked';
      }

      $aff_legend .= "<tr><td width='15%' style=\"border: 4px solid #fff;\">";

      $aff_legend .= "<table style=\"border-collapse: collapse; margin: 0; padding: 0;\"><tr>";
      if ($brighten_negative) {
        $aff_legend .= "<td width=\"9\" height=\"18\" style=\"background-color: #{$link['color']}\">&nbsp;</td>";
        $aff_legend .= "<td width=\"9\" height=\"18\" style=\"opacity: 0.73; background-color: #{$link['color']}\">&nbsp;</td>";
      } else {
        $aff_legend .= "<td width=\"18\" height=\"18\" style=\"background-color: #{$link['color']}\">&nbsp;</td>";
      }
      $aff_legend .= "</tr></table>";

      $aff_legend .= "</td><td>&nbsp;" . $link['descr'] . "</td>";
      $aff_legend .= "<td>&nbsp;<input type='checkbox' name='".$tag."' id ='".$tag."' ".$checked."></td>";
      $aff_legend .= "</tr>\n";
    }

    $aff_legend .= "</table>";

    $topas = getasstats_top($ntop, $statsfile, $selected_links, $as_num);

    // FORMATTING DATA
    foreach( $as_num as $as ) {
      if ( !isset($topas[$as]) ) { $topas[$as] = ""; }
    }

    foreach( $topas as $as => $nbytes ) {
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

      $aff_astable .= '<div class="small">In the last '. $label . '</div>';
      if ( !isset($nbytes[0]) ) $nbytes[0] = 0;
      if ( !isset($nbytes[1]) ) $nbytes[1] = 0;
      if ( !isset($nbytes[2]) ) $nbytes[2] = 0;
      if ( !isset($nbytes[3]) ) $nbytes[3] = 0;

   	  $aff_astable .= '<div class="small">IPv4: ~ '.format_bytes($nbytes[0]).' in / ' . format_bytes($nbytes[1]) . '</div>';
  	  if ($showv6) {
  	    $aff_astable .= '<div class="small">IPv6: ~ '.format_bytes($nbytes[2]).' in / ' . format_bytes($nbytes[3]) . '</div>';
  	  }

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

      $rrdfile = getRRDFileForAS($as, $peerusage);
      if ( file_exists($rrdfile) ) {
        $img_v4 = '<span class="pull-right">' . getHTMLUrl($as, 4, $asinfo['descr'], $start, $end, $peerusage, $selected_links) . '</span>';
        if ($showv6) $img_v6 = '<span class="pull-right">' . getHTMLUrl($as, 6, $asinfo['descr'], $start, $end, $peerusage, $selected_links) . '</span>';
      } else { $img_v4 = $img_v6 = ""; }

      if ( !$img_v4 ) $img_v4 = "<center>No data found for AS".$as."</center>";
      if ( !$img_v6 ) $img_v6 = "<center>No data found for AS".$as."</center>";

      if ($showv6) { $col = "5"; } else { $col="10"; }
      $aff_astable .= '<div class="col-lg-'.$col.'">';
      $aff_astable .= $img_v4;
      $aff_astable .= '</div>';

      if ($showv6) {
        $aff_astable .= '<div class="col-lg-5">';
        $aff_astable .= $img_v6;
        $aff_astable .= '</div>';
      }

      $aff_astable .= '</div>';

      $aff_astable .= '</li>';

      $i++;
    }
    $aff_astable .= '</ul>';
  } else {
    $aff_astable .= '<div class="alert alert-info">';
    $aff_astable .= '<h4><i class="icon fa fa-warning"></i> Alert!</h4>';
    $aff_astable .= 'No data for AS-SET <b>' . $asset . '</b>';
    $aff_astable .= '</div>';
  }
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
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="css/custom.css">
</head>
<body class="hold-transition skin-black-light sidebar-collapse layout-top-nav fixed" <?php echo $select_form; ?>>

<div class="wrapper">

  <!-- =============================================== -->
  <?php echo menu($selected_links); ?>
  <!-- =============================================== -->

  <div class="content-wrapper">
    <?php echo content_header($header, $header_small . '('.$label.')'); ?>

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

          <?php if ( $aff_legend ) { ?>
          <div class="row">
            <div class="col-lg-12">

              <form method='get'>
                <input type='hidden' name='asset' value='<?php echo $asset; ?>'/>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Legend</h3>
                  </div>
                  <div class="box-body">
                    <?php echo $aff_legend; ?>
                  </div>
                  <div class="box-footer">
                    <button type="submit" class="btn pull-right"><i class="fa fa-search"></i></button>
                  </div>
                </div>
              </form>

            </div>
          </div>
          <?php } ?>

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
<script src="plugins/fastclick/fastclick.min.js"></script>
<script src="dist/js/app.min.js"></script>

</body>
</html>
