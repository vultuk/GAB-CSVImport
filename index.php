<?php

	error_reporting(E_ALL);

	require_once('class.database.php');
	require_once('class.csv.php');
	
	$csvFile = new csv();
	
	
	
	if (!$csvFile->load(1)) {
		print "No file found";
	} else {
		$csvFile->setNumberColumns( Array(2, 4) );
		
		print $csvFile->getMainNumber(1);
		
		print " - ";
		
		print $csvFile->getAltNumber(1);
		
	}


	
	

?>





