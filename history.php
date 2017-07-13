<?php include("func.inc"); ?>

<?php
$selected_links = array();
$val_searchas = isset($_GET['as']) ? $_GET['as'] : "";
$aff_customlinks = "";

if ( isset($_GET['as']) ) {
  $as = str_replace('as','',str_replace(' ','',strtolower($_GET['as'])));
  if ($as) $asinfo = getASInfo($as);

  $title = "AS-Stats | History for AS".$as.": ".$asinfo['descr'];
  $header = 'History for AS' . $as;
  $header_small = $asinfo['descr'];
  $select_form = "";

  if(isset($_GET['peerusage']) && $_GET['peerusage'] == '1') { $peerusage = 1; }
  else { $peerusage = 0; }

  $rrdfile = getRRDFileForAS($as, $peerusage);

  $daily_graph_v4 = getHTMLImg($as, 4, $asinfo['descr'], time() - 24 * 3600, time(), $peerusage, 'daily graph', 'detailgraph', true);
  $weekly_graph_v4 = getHTMLImg($as, 4, $asinfo['descr'], time() - 7 * 86400, time(), $peerusage, 'weekly graph', 'detailgraph', true);
  $monthly_graph_v4 = getHTMLImg($as, 4, $asinfo['descr'], time() - 30 * 86400, time(), $peerusage, 'monthly graph', 'detailgraph', true);
  $yearly_graph_v4 = getHTMLImg($as, 4, $asinfo['descr'], time() - 365 * 86400, time(), $peerusage, 'yearly graph', 'detailgraph', true);

  if ($showv6) {
    $daily_graph_v6 = getHTMLImg($as, 6, $asinfo['descr'], time() - 24 * 3600, time(), $peerusage, 'daily graph', 'detailgraph', true);
    $weekly_graph_v6 = getHTMLImg($as, 6, $asinfo['descr'], time() - 7 * 86400, time(), $peerusage, 'weekly graph', 'detailgraph', true);
    $monthly_graph_v6 = getHTMLImg($as, 6, $asinfo['descr'], time() - 30 * 86400, time(), $peerusage, 'monthly graph', 'detailgraph', true);
    $yearly_graph_v6 = getHTMLImg($as, 6, $asinfo['descr'], time() - 365 * 86400, time(), $peerusage, 'yearly graph', 'detailgraph', true);
  }

  if ( !empty($customlinks) ) {
    $aff_customlinks .= '<div class="list-group list-group-unbordered">';
    foreach ($customlinks as $linkname => $url) {
      $url = str_replace("%as%", $as, $url);
      $aff_customlinks .= '<a href='.$url.' target="_blank" class="list-group-item"><i class="fa fa-external-link text-blue"></i> ' . htmlspecialchars($linkname) . '</a>';
    }
    $aff_customlinks .= "</div>";
  }
} else {
  $title = "AS-Stats | History";
  $header = 'History for AS';
  $header_small = "";

  $select_form = 'onload="document.forms[0].as.focus(); document.forms[0].as.select();"';
}
?>

<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Refresh" content="300">
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
    <?php echo content_header($header, $header_small); ?>

    <section class="content">
      <div class="row">

        <div class="col-lg-2">

          <div class="row">
            <div class="col-lg-12">

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Search AS</h3>

                  <div class="box-tools">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <form class="navbar-form navbar-left" role="search">
                  <div class="input-group">
                  <input type="text" class="form-control menu-input" name="as" placeholder="Search AS" value="<?php echo $val_searchas; ?>">
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

              <?php if ($aff_customlinks) { ?>
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Custom Links</h3>

                  <div class="box-tools">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <?php echo $aff_customlinks; ?>
                </div>
              </div>
              <?php } ?>

            </div>
          </div>

        </div>

        <?php if ( isset($_GET['as'])) { ?>
        <div class="col-lg-10">
          <div class="row">
            <div class="col-lg-12">

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Daily</h3>

                  <div class="box-tools">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <center>
                  <?php $col = $showv6 ? "6" : "12"; ?>
                  <div class="col-lg-<?php echo $col; ?>">
                    <?php echo $daily_graph_v4; ?>
                  </div>

                  <?php if ($showv6) { ?>
                  <div class="col-lg-6">
                    <?php echo $daily_graph_v6; ?>
                  </div>
                  <?php } ?>
                  </center>
                </div>
              </div>

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Weekly</h3>

                  <div class="box-tools">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <center>
                  <?php $col = $showv6 ? "6" : "12"; ?>
                  <div class="col-lg-<?php echo $col; ?>">
                    <?php echo $weekly_graph_v4; ?>
                  </div>

                  <?php if ($showv6) { ?>
                  <div class="col-lg-6">
                    <?php echo $weekly_graph_v6; ?>
                  </div>
                  <?php } ?>
                  </center>
                </div>
              </div>

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Monthly</h3>

                  <div class="box-tools">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <center>
                  <?php $col = $showv6 ? "6" : "12"; ?>
                  <div class="col-lg-<?php echo $col; ?>">
                    <?php echo $monthly_graph_v4; ?>
                  </div>

                  <?php if ($showv6) { ?>
                  <div class="col-lg-6">
                    <?php echo $monthly_graph_v6; ?>
                  </div>
                  <?php } ?>
                  </center>
                </div>
              </div>

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Yearly</h3>

                  <div class="box-tools">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <center>
                  <?php $col = $showv6 ? "6" : "12"; ?>
                  <div class="col-lg-<?php echo $col; ?>">
                    <?php echo $yearly_graph_v4; ?>
                  </div>

                  <?php if ($showv6) { ?>
                  <div class="col-lg-6">
                    <?php echo $yearly_graph_v6; ?>
                  </div>
                  <?php } ?>
                  </center>
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
