<?php include("func.inc"); ?>

<?php
$selected_links = array();
$knownlinks = getknownlinks();
$hours = 24;
if (@$_GET['numhours'])
	$hours = (int)$_GET['numhours'];
$label = statsLabelForHours($hours);

$i = 0;

foreach ($knownlinks as $link) {
	$class = (($i % 2) == 0) ? "" : "even";

	if ($showv6) {
		$list_img[$link['tag']] = '<img alt="link graph" src="linkgraph.php?link='.$link['tag'].'&numhours='.$hours.'&width='.$linkusage_graph_width.'&height='.$linkusage_graph_height.'&dname='.rawurlencode($link['descr'] . " - IPv4").'" width="'.$linkusage_graph_width.'" height="'.$linkusage_graph_height.'" border="0" />';
		$list_img_v6[$link['tag']] = '<img alt="link graph" src="linkgraph.php?link='.$link['tag'].'&numhours='.$hours.'&width='.$linkusage_graph_width.'&height='.$linkusage_graph_height.'&dname='.rawurlencode($link['descr'] . " - IPv6").'&v=6" width="'.$linkusage_graph_width.'" height="'.$linkusage_graph_height.'" border="0" />';
	} else {
		$list_img[$link['tag']] = '<img alt="link graph" src="linkgraph.php?link='.$link['tag'].'&numhours='.$hours.'&width='.$linkusage_graph_width.'&height='.$linkusage_graph_height.'&dname='.rawurlencode($link['descr']).'" width="'.$linkusage_graph_width.'" height="'.$linkusage_graph_height.'" border="0" />';
	}

	if ( ($showtitledetail && !$hidelinkusagename) || (!$showtitledetail) ) {
		$txt_title[$link['tag']] = $link['descr'];
	} else { $txt_title = ""; }
}
?>


<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AS-Stats | Link Usage - Top 10 AS per link (<?php echo $label ?>)</title>
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
<body class="hold-transition skin-black-light sidebar-collapse layout-top-nav">

<div class="wrapper">

  <!-- =============================================== -->
  <?php echo menu($selected_links); ?>
  <!-- =============================================== -->

  <div class="content-wrapper">
    <?php echo content_header('Link Usage', 'Top 10 AS per link ('.$label.')'); ?>

    <section class="content">
			<div class="row">
				<?php
					if ( !$txt_title ) {
						if (!$showv6) {
							foreach ($list_img as $key => $img) {
								echo '<div class="col-lg-6">';
								echo box_linkusage('Link Usage', $img);
								echo '</div>';
							}
						} else {
							foreach ($list_img as $key => $img) {
								echo '<div class="col-lg-12">';
								echo box_linkusage('Link Usage', $img . ' ' . $list_img_v6[$key]);
								echo '</div>';
							}
						}
					} else {
						if (!$showv6) {
							foreach ($list_img as $key => $img) {
								echo '<div class="col-lg-6">';
								echo box_linkusage('Link Usage for ' . $txt_title[$key], $img);
								echo '</div>';
							}
						} else {
							foreach ($list_img as $key => $img) {
								echo '<div class="col-lg-12">';
								echo box_linkusage('Link Usage for ' . $txt_title[$key], $img . ' ' . $list_img_v6[$key]);
								echo '</div>';
							}
						}
					}
				?>
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
