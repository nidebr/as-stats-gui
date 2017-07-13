<?php include("func.inc"); ?>

<?php
if(!isset($peerusage)) $peerusage = 0;
if (isset($_GET['n'])) $ntop = (int)$_GET['n'];
if ($ntop > 200) $ntop = 200;
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

foreach($knownlinks as $link){
	if(isset($_GET["link_${link['tag']}"]))
		$selected_links[] = $link['tag'];
}
$topas = getasstats_top($ntop, $statsfile, $selected_links);
$start = time() - $hours*3600;
$end = time();

if ($showv6) { $first_col = "1"; $second_col = "11"; $offset_second_col = "0";  } else { $first_col = "2"; $second_col = "9"; $offset_second_col = "1"; }

// Mobile Detect for show legend
$detect = new Mobile_Detect;

$i = 0;
$aff_astable = '<ul class="nav nav-stacked">';

foreach ($topas as $as => $nbytes) {
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
  $aff_astable .= '<span class="small">' . join(" | ", $htmllinks) . '</span>';

  // RANK
  $aff_astable .= '<div class="rank">';
	$aff_astable .= '#' . ($i+1);
	$aff_astable .= '</div>';

  $aff_astable .= '</div>';

  if ($showv6) { $col = "5"; } else { $col="10"; }
  $aff_astable .= '<div class="col-lg-'.$col.'">';
  $aff_astable .= '<span class="pull-right">';
  $aff_astable .= getHTMLUrl($as, 4, $asinfo['descr'], $start, $end, $peerusage, $selected_links);
  $aff_astable .= '</span>';
  $aff_astable .= '</div>';

  if ($showv6) {
    $aff_astable .= '<div class="col-lg-5">';
    $aff_astable .= '<span class="pull-right">';
    $aff_astable .= getHTMLUrl($as, 6, $asinfo['descr'], $start, $end, $peerusage, $selected_links);
    $aff_astable .= '</span>';
    $aff_astable .= '</div>';
  }

  $aff_astable .= '</div>';

  $aff_astable .= '</li>';

  $i++;
}

$aff_astable .= '</ul>';

// LEGEND
if ( !$detect->isMobile() && !$detect->isTablet() ) {
  $aff_legend = "<table class='small'>";

  foreach ($knownlinks as $link) {
    $tag = "link_${link['tag']}";

    $checked = '';
    if(isset($_GET[$tag]) && $_GET[$tag] == 'on') {
      $checked = 'checked';
    }

  	$aff_legend .= "<tr><td style=\"border: 4px solid #fff;\">";

  	$aff_legend .= "<table style=\"border-collapse: collapse; margin: 0; padding: 0\"><tr>";
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
} else {
  $aff_legend = "<table class='small'>";
  $aff_legend .= "<tr>";
  $aff_legend .= "<td style=\"border: 4px solid #fff;\">";

  $aff_legend .= "<table style=\"border-collapse: collapse; margin: 0; padding: 0\"><tr>";
  foreach ($knownlinks as $link) {
    $tag = "link_${link['tag']}";

    $checked = '';
    if(isset($_GET[$tag]) && $_GET[$tag] == 'on') {
      $checked = 'checked';
    }

    if ($brighten_negative) {
  		$aff_legend .= "<td width=\"9\" height=\"18\" style=\"background-color: #{$link['color']}\">&nbsp;</td>";
  		$aff_legend .= "<td width=\"9\" height=\"18\" style=\"opacity: 0.73; background-color: #{$link['color']}\">&nbsp;</td>";
  	} else {
  		$aff_legend .= "<td width=\"18\" height=\"18\" style=\"background-color: #{$link['color']}\">&nbsp;</td>";
  	}
    $aff_legend .= "<td>&nbsp;" . $link['descr'] . "&nbsp;</td>\n";

    $aff_legend .= "<td>&nbsp;<input type='checkbox' name='".$tag."' id ='".$tag."' ".$checked.">&nbsp;</td>";
  }
  $aff_legend .= "</tr></table>";

  $aff_legend .= "</td>";
  $aff_legend .= "</tr>";
  $aff_legend .= "</table>";
}

?>

<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Refresh" content="300">
  <title>AS-Stats | Top <?php echo $ntop; ?> AS<?php if($peerusage) echo " peer"; ?> (<?php echo $label?>)</title>
  <link rel="icon" href="favicon.ico" />
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/font-awesome/font-awesome.min.css">
  <link rel="stylesheet" href="plugins/ionicons/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="css/custom.css">
</head>
<body class="hold-transition skin-black-light sidebar-collapse layout-top-nav fixed">

<div class="wrapper">

  <!-- =============================================== -->
  <?php echo menu($selected_links); ?>
  <!-- =============================================== -->

  <div class="content-wrapper">
    <?php echo content_header('Top ' . $ntop . ' AS', '('.$label.')'); ?>

    <section class="content">

      <div class="row">

        <div class="col-md-12 col-lg-<?php echo $first_col; ?>">
          <div class="row">

            <div class="col-lg-12">
              <?php
                if ( $detect->isMobile() || $detect->isTablet() ) {
              ?>

              <form method='get'>
                <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
                <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
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

              <?php
                } else {
              ?>

              <div class="row affix col-md-12 col-lg-<?php echo $first_col; ?>">

                <form method='get'>
                  <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
                  <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
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

              <?php } ?>
            </div>

          </div>
        </div>

        <div class="col-md-12 col-lg-<?php echo $second_col; ?> col-lg-offset-<?php echo $offset_second_col; ?>">

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
