<?php include("func.inc"); ?>

<?php
$aff_astable = $select_topinterval = "";

if(!isset($peerusage)) $peerusage = 0;
if (isset($_GET['n'])) $ntop = (int)$_GET['n'];
if ($ntop > 200) $ntop = 200;
$hours = 24;

if (isset($_GET['ix'])) { $ix_id = (int)$_GET['ix']; } else { $ix_id = ""; }
if (isset($_GET['name_ix'])) { $name_ix = $ix_name = $_GET['name_ix']; } else { $name_ix = $ix_name =""; }

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
$detect = new Mobile_Detect;

foreach($knownlinks as $link){
	if(isset($_GET["link_${link['tag']}"]))
		$selected_links[] = $link['tag'];
}

if ($showv6) { $first_col = "1"; $second_col = "11"; $offset_second_col = "0";  } else { $first_col = "2"; $second_col = "9"; $offset_second_col = "1"; }

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

$peerdb = new PeeringDB();

if ( $my_asn ) {
  // SELECT IX FROM PEERINGDB
  $list_ix = $peerdb->GetIX($my_asn);
  $select_ix = '<select name="ix" id="ix" class="form-control" onchange="this.form.submit()">';
  $select_ix .= '<option value="">Select IX</option>';
  foreach ($list_ix as $key => $value) {
    if ( isset($ix_id) ) {
      if ( $value->ix_id == $ix_id ) {
        $selected = "selected";
  			$ix_name = $value->name . " - ";
      } else {
        $selected = "";
      }
    } else { $selected = ""; }

    $select_ix .= '<option '.$selected.' value="'.$value->ix_id.'">'.$value->name.'</option>';
  }
  $select_ix .= '</select>';
}

if ( $ix_id ) {
	$list_asn = $peerdb->GetIXASN($ix_id);
	$topas = getasstats_top($ntop, $statsfile, $selected_links, $list_asn);
	$start = time() - $hours*3600;
	$end = time();

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

	// TOP INTERVAL SELECT
  if ( count($top_intervals) > 1 ) {
  	$select_topinterval = '<select name="numhours" id="numhours" class="form-control" onchange="this.form.submit()">';
  	foreach ($top_intervals as $interval) {
  		$selected = isset($_GET['numhours']) && $_GET['numhours'] == $interval['hours'] ? "selected" : "";
  		$select_topinterval .= '<option '.$selected.' value="'.$interval['hours'].'">'.$interval['label'].'</option>';
  	}
  	$select_topinterval .= '</select>';
  }
}
?>

<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Refresh" content="300">
  <title>AS-Stats | Top IX</title>
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
    <?php echo content_header($ix_name . ' Top ' . $ntop . ' AS', '('.$label.')'); ?>

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
								<input type='hidden' name='ix' value='<?php echo $ix_id; ?>'/>
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
									<input type='hidden' name='ix' value='<?php echo $ix_id; ?>'/>
									<input type='hidden' name='name_ix' value='<?php echo $name_ix; ?>'/>
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
            <?php if ( $my_asn ) { ?>
						<div class="col-md-12 col-lg-4">
							<form method='get'>
                <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
                <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">My IX</h3>
                  </div>
                  <div class="box-body">
                  	<?php echo $select_ix; ?>
                  </div>
                </div>
              </form>
						</div>
            <?php } ?>
						<div class="col-md-12 col-lg-4">
							<form method='get' id="search_ix_name">
                <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
                <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Search IX</h3>
                  </div>
                  <div class="box-body">
                    <?php $val_name_ix = isset($_GET['name_ix']) ? $_GET['name_ix'] : ""; ?>
                    <input type="text" class="form-control" name="name_ix" placeholder="Search IX" id="peeringdb" data-provide="typeahead" autocomplete="off" value="<?php echo $val_name_ix; ?>">
                    <input type='hidden' id='ix' name='ix'/>
                    <div id="message"></div>
                  </div>
                </div>
              </form>
						</div>
						<?php if ( $aff_astable ) { ?>
            <?php if ( $select_topinterval ) { ?>
						<div class="col-md-12 col-lg-4">
							<form method='get'>
                <input type='hidden' name='ix' value='<?php echo $ix_id; ?>'/>
                <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
								<input type='hidden' name='name_ix' value='<?php echo $name_ix; ?>'/>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Interval</h3>
                  </div>
                  <div class="box-body">
                  	<?php echo $select_topinterval; ?>
                  </div>
                </div>
              </form>
						</div>
            <?php } ?>
						<div class="col-md-12">
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
<script src="plugins/jQueryUI/jquery-ui.min.js"></script>
<script src="plugins/typeahead/bootstrap3-typeahead.min.js"></script>

<script type="text/javascript">
  $(document).ready(function(){
    $('#peeringdb').typeahead({
      source: function (query, process) {
        $.ajax({
          url: 'lib/json/get_ixname.php',
          dataType: 'JSON',
          minLength: 2,
          data: 'name=' + query,
          success: function(data) {
						if ( data !== null ) {
            	process(data);
						} else {
							$("#message").html('<small class="form-text text-muted">No IX found.</small>');
						}
          },
          beforeSend: function () {
             $("#peeringdb").addClass("searchBox");
          },
          complete: function () {
             $("#peeringdb").removeClass("searchBox");
          },
        });
      },
      updater : function (item) {
				$("form input[name=ix]").val(item.id);
				//$("#ix").val("");
				this.$element[0].value = item.name;
      	this.$element[0].form.submit();
        return item.name;
      },
      autoselect: true,
    });
  });
</script>

</body>
</html>
