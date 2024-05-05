<?php

/* DO NOT EDIT THIS FILE - override these variables in config.inc instead */

/* Default graph size if no parameters are specified */
$default_graph_width = 600;
$default_graph_height = 360;

/* Size of graphs on top N AS page */
$top_graph_width = 600;
$top_graph_height = 220;

/* Size of graphs on AS-Set page */
$asset_graph_width = 600;
$asset_graph_height = 200;

/* Defaults for other settings, introduced in recent versions */
$vertical_label = true;			# vertical IN/OUT label in graph
$brighten_negative = true;		# brighten the "negative" part of graphs
$showpeeras = false;

/* Time intervals for top N AS */
$top_intervals = array(
	array(
		'hours' => 24,
		'label' => '24 hours'
	)
);