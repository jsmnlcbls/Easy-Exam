<?php
include '/functions/common.php';

$reviewCategory = filterGet("reviewCategory", "");
$view = "";
if ("" != $reviewCategory) {
	$view = "questions";
}
include '/views/indexView.php';

