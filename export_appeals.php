<?php

	date_default_timezone_set('America/New_York');
	ini_set('display_errors', true);
	error_reporting(-1);

	require('sccourtopinions/opinion.php');
	require('sccourtopinions/sccourtopinions.php');
	require('sccourtopinions/appealscourt.php');

	$opinions = SCCourtOpinions\AppealsCourt::factory()->opinions();

	print_r( $opinions );

?>