<?php

	system("clear");

	$time = microtime(); 
	$time = explode(" ", $time); 
	$time = $time[1] + $time[0]; 
	$start = $time; 

	error_reporting(0);
	ini_set('memory_limit','2048M');
	
	require_once('class.database.php');
	require_once('class.csv.php');
	
	
	$options = getopt("s:f:y:z:m:a:t:");
	
	
	function runConversionScript($incReportType="F", $incFilename="F", $incMainNumber="F", $incAltNumber="F", $incFirstName="F", $incSurname="F") {
		GLOBAL $options, $start, $csvFile;
	
		if ($incFilename == "F") $incFilename = $options['f'];
		if ($incMainNumber == "F") $incMainNumber = $options['m'];
		if ($incAltNumber == "F") $incAltNumber=$options['a'];
		if ($incFirstName == "F") $incFirstName=$options['y'];
		if ($incSurname == "F") $incSurname=$options['z'];
	

		
		$mobileOnly = $incReportType;
		$ext = ".".substr(strrchr($incFilename,'.'),1);
		$filename = str_replace($ext,"",$incFilename);
		
		print "Loading Leads\n";
		
		if (!isset($csvFile)) {
			$csvFile = new csv();
			$loadFile = $csvFile->load($filename.$ext);
		} else {
			$loadFile = TRUE;
		}
		
		if (!$loadFile) {
			print "No file found";
		} else {
			$csvFile->setNumberColumns( Array($incMainNumber,$incAltNumber) );
			$csvFile->setNameColumns( $incFirstName, $incSurname );
			
			
			
			$fh = fopen($filename."_".$mobileOnly.".txt", 'w') or die("can't open file");
			
			print "Writing ".$count." Leads\n\n";
			
			$OutputReport = "";
			
			$MissingNamesCount = 0;
			$MissingNumbersCount = 0;
			
			$validNumbers = 0;
			
			$headings = $csvFile->getFileHeadings();
			
			unset($headings[(int)$incMainNumber]);
			unset($headings[(int)$incAltNumber]);
			
			unset($headings[(int)$incSurname]);
			unset($headings[(int)$incFirstName]);
			
			
			$headingString = "";
			foreach($headings AS $heading)
			{
				$headingString .= $heading."\t";
			}
			
			$headingString .= "GAB First Name\tGAB Surname\t";
			$headingString .= "GAB Main Number\tGAB Alt Number";
			
			$OutputReport .= $headingString."\tReason\n";
			
			fwrite($fh, $headingString."\n");
			
			$count = $csvFile->countRows();
			
			for ($i = 1; $i <= $count; $i++) {
	    		
	    		$AddLine = True;
	    		$reason = "";
	    		$singleLine = "";
	    		
	    		
	    		$firstName = $csvFile->getFirstName($i);
	    		$surname = $csvFile->getSurname($i);
	    		
	    		$mainNumber = $csvFile->getMainNumber($i, $mobileOnly);
	    		$altNumber = $csvFile->getAltNumber($i, $mobileOnly);
	    		
	    		foreach ($headings AS $key => $heading) {
	    			$singleLine .= $csvFile->getColumn($i, $key)."\t";
	    		}
	    		
	    		
	    		
	    		if ( !$mainNumber AND !$altNumber ) {
	    			// Check that we have at least 1 numbers
	    			$AddLine = FALSE;
	    			$reason = "No valid phone numbers available";
	    			$MissingNumbersCount++;
	    		} else if ( !$firstName AND !$surname ) {
	    			// Check that we have at least 1 name
	    			$AddLine = FALSE;
	    			$reason = "No name available";
	    			$MissingNamesCount++;
	    		}
	    		
	    		
	    		if ($AddLine) {
	    			$validNumbers++;
	    			$singleLine .= $firstName."\t".$surname."\t";
	    			$singleLine .= $mainNumber."\t".$altNumber;
	    			fwrite($fh, $singleLine."\n");
	  				print "-";
	  			} else {
	  				$singleLine .= $csvFile->getColumn($i, (int)$incFirstName)."\t".$csvFile->getColumn($i, (int)$incSurname)."\t";
	  				$singleLine .= $csvFile->getColumn($i, (int)$incMainNumber)."\t".$csvFile->getColumn($i, (int)$incAltNumber);
	  				$OutputReport .= $singleLine."\t".$reason."\n";
	  				print "!";
	  			}
	    		
	
	    		if (($i % 100) == 0) {
	    			print " - ".$i."\n";
	    		}
	    				
			}
			
			fclose($fh);
			
			print "\n\nAll Done!\n\n";
			
			
			$time = microtime(); 
			$time = explode(" ", $time); 
			$time = $time[1] + $time[0]; 
			$finish = $time; 
			$totaltime = ($finish - $start); 
			
			
			print "Processing Time: ".$totaltime." seconds\n\n";
			
			print "Invalid Numbers: ".$MissingNumbersCount."\nInvalid Names: ".$MissingNamesCount."\n\nValid Leads: ".$validNumbers;
			
			print "\n\n";
			
			
			$fh = fopen($filename."_".$mobileOnly."_ErrorReport.csv", 'w') or die("can't open file");
			fwrite($fh, str_replace("\t",",",$OutputReport));
			fclose($fh);
			
		}
	
	}
	
	
	
	
	
	
	if (count($options) < 3 AND isset($options['t'])) {
		
		if ($options['t'] == "headers") {
		
			$csvFile = new csv();
			
			$ext = ".".substr(strrchr($options['f'],'.'),1);
			$filename = str_replace($ext,"",$options['f']);
			
			if (!$csvFile->load($filename.$ext)) {
				print "No file found";
			} else {
			
				print "Headers for this file are..\n\n";
				
				$headings = $csvFile->getFileHeadings();
				
				foreach ($headings AS $key => $head) {
					print $key." : ".$head."\n";
				}
				
				print "\n";
			
			}
		
		} else if ($options['t'] == "wizard") {
			
			$csvFile = new csv();
			
			$ext = ".".substr(strrchr($options['f'],'.'),1);
			$filename = str_replace($ext,"",$options['f']);
			
			if (!$csvFile->load($filename.$ext)) {
				print "No file found";
			} else {
			
				
				
				$headings = $csvFile->getFileHeadings();
				
				
				$headingsValid = FALSE;
				$headingsChanged = FALSE;
				
				while (!$headingsValid) {
					
					system("clear");
					print "\n";
					
					print "Headers for this file are..\n\n";
					
					foreach ($headings AS $key => $head) {
						print $key." : ".$head."\n";
					}
					
					print "\n";
					
					print "Are these headings correct? (Y/N) : ";
					
					$correctHeadings = fgets(STDIN);
					
					if (str_replace("\n","",strtoupper($correctHeadings)) == "Y") {
						$headingsValid = TRUE;
						if ($headingsChanged) {
							$csvFile->setHeadings($headings);
						}
					} else {
					
						print "\n";
						
						$headings = $csvFile->getFileHeadings();
						
						foreach ($headings AS $key => $heading) {
						
							print "Title for column ".$key." : ";
							$headings[$key] = str_replace("\n","",fgets(STDIN));
						
						}
						
						$headingsChanged = TRUE;
					
					
					}
					
					
					print "\n";

				}
				
				
			
			}
			
			
			

			
			print "Enter conversion type (all, both, mobile, landline) : ";
			$entConType = fgets(STDIN);
			
			print "Enter Main Number column id : ";
			$entMainCol = fgets(STDIN);
			
			print "Enter Alt Number column id : ";
			$entAltCol = fgets(STDIN);
			
			print "Enter First Name column id : ";
			$entFirstName = fgets(STDIN);
			
			print "Enter Surname column id : ";
			$entSurname = fgets(STDIN);
			
			print "\n";
			
			
			// $incReportType=FALSE, $incFilename=FALSE, $incMainNumber=FALSE, $incAltNumber=FALSE, $incFirstName=FALSE, $incSurname=FALSE



			if (str_replace("\n","",strtoupper($entConType)) == "BOTH") {
				runConversionScript(str_replace("\n","",strtoupper("mobile")), $options['f'], (int)$entMainCol, (int)$entAltCol, (string)str_replace("\n","",$entFirstName), (string)str_replace("\n","",$entSurname));
				runConversionScript(str_replace("\n","",strtoupper("landline")), $options['f'], (int)$entMainCol, (int)$entAltCol, (string)str_replace("\n","",$entFirstName), (string)str_replace("\n","",$entSurname));
			} else {
				runConversionScript(str_replace("\n","",strtoupper($entConType)), $options['f'], (int)$entMainCol, (int)$entAltCol, (string)str_replace("\n","",$entFirstName), (string)str_replace("\n","",$entSurname));
			}
			
		}
		
	} else if (count($options) < 3) {
		// If there's no arguments then explain!
		print "No arguments given\n\n";
	
		print "-s ABC          : Output sample (ALL, BOTH, MOBILE, LANDLINE)"."\n";
		print "-f abc.csv      : Input filename"."\n";
		print "-m x            : Main number column id"."\n";
		print "-a x            : Alt number column id"."\n";
		print "-y x            : First name column id"."\n";
		print "-z x            : Surname column id"."\n";
		
		print "\n\n";
	} else {
	
		// $incReportType=FALSE, $incFilename=FALSE, $incMainNumber=FALSE, $incAltNumber=FALSE, $incFirstName=FALSE, $incSurname=FALSE
	
		runConversionScript();
		
	}
	
	
	
	
	
	
		

?>