<?php

	date_default_timezone_set('America/New_York');
	ini_set('display_errors', true);
	error_reporting(-1);

	require('sccourtopinions/opinion.php');
	require('sccourtopinions/sccourtopinions.php');
	require('sccourtopinions/supremecourt.php');

	$opinions = SCCourtOpinions\SupremeCourt::factory()->opinions( 2014, 2 );

	print_r( $opinions );

?>