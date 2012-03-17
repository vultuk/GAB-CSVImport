<?php

	class csv
	{
		
		var $_data;
		var $_dataLoaded;
		var $_numberColumns;
		var $_nameColumns;
		
		var $numberOfRows;
		
		
		private function checkUKTelephone(&$strTelephoneNumber, &$intError, &$strError) {
		  
		  // Copy the parameter and strip out the spaces
		  $strTelephoneNumberCopy = str_replace (' ', '', $strTelephoneNumber);
		
		  // Convert into a string and check that we were provided with something
		  if (empty($strTelephoneNumberCopy)) {
		    $intError = 1;
		    $strError = 'Telephone number not provided';
		    return false;
		  }
		  
		  // Don't allow country codes to be included (assumes a leading "+") 
		  if (preg_match('/^(\+)[\s]*(.*)$/',$strTelephoneNumberCopy)) {
		    $intError = 2;
		    $strError = 'UK telephone number without the country code, please';
		    return false;
		  }
		  
		  // Remove hyphens - they are not part of a telephone number
		  $strTelephoneNumberCopy = str_replace ('-', '', $strTelephoneNumberCopy);
		  
		  // Now check that all the characters are digits
		  if (!preg_match('/^[0-9]{10,11}$/',$strTelephoneNumberCopy)) {
		    $intError = 3;
		    $strError = 'UK telephone numbers should contain 10 or 11 digits';
		    return false;
		  }
		  
		  // Now check that the first digit is 0
		  if (!preg_match('/^0[0-9]{9,10}$/',$strTelephoneNumberCopy)) {
		    $intError = 4;
		    $strError = 'The telephone number should start with a 0';
		    return false;
		  }		
		  
		  // Check the string against the numbers allocated for dramas
		  
		  // Expression for numbers allocated to dramas
			
			$tnexp[0] =  '/^(0113|0114|0115|0116|0117|0118|0121|0131|0141|0151|0161)(4960)[0-9]{3}$/';
			$tnexp[1] =  '/^02079460[0-9]{3}$/';
			$tnexp[2] =  '/^01914980[0-9]{3}$/';
			$tnexp[3] =  '/^02890180[0-9]{3}$/';
			$tnexp[4] =  '/^02920180[0-9]{3}$/';
			$tnexp[5] =  '/^01632960[0-9]{3}$/';
			$tnexp[6] =  '/^07700900[0-9]{3}$/';
			$tnexp[7] =  '/^08081570[0-9]{3}$/';
			$tnexp[8] =  '/^09098790[0-9]{3}$/';
			$tnexp[9] =  '/^03069990[0-9]{3}$/';
			
		  foreach ($tnexp as $regexp) {  
		    if (preg_match($regexp,$strTelephoneNumberCopy, $matches)) {
		      $intError = 5;
		      $strError = 'The telephone number is either invalid or inappropriate';
		      return false;
		    }
		  }
		  
		  // Finally, check that the telephone number is appropriate.
		  if (!preg_match('/^(01|02|03|05|070|071|072|073|074|075|07624|077|078|079)[0-9]+$/',$strTelephoneNumberCopy)) {
		    $intError = 5;
		    $strError = 'The telephone number is either invalid or inappropriate';
		    return false;
		  }
		  
		  // Seems to be valid - return the stripped telephone number
		  $strTelephoneNumber = $strTelephoneNumberCopy;
		  $intError = 0;
		  $strError = '';
		  return true;  
		}
		
		
		
		/**
		 * Function to check a telephone number is a valid UK number.
		 * 
		 * @access private
		 * @param mixed $number
		 * @param string $numberType (default: "ALL")
		 * @return void
		 */
		private function checkNumberValid($number, $numberType="ALL")
		{
		
			// Create an array to hold our valid digits
			$valid = Array();
			
			// Valid UK telephone number digits
			$mobileArray = Array(7);
			$landlineArray = Array(1,2);
			
			// Strip spaces from the number
			$thisNumber = (int)str_replace(" ","",$number);
			
			// Make sure the number starts with a 0
			if ( substr($thisNumber, 0, 1) != "0" ) {
				$thisNumber = "0".$thisNumber;
			}
			
			// Create a selection of valid digits depending on the type required
			if ($numberType == "ALL") {
				$valid = array_merge($mobileArray, $landlineArray);
			} else if ($numberType == "MOBILE") {
				$valid = $mobileArray;
			} else if ($numberType == "LANDLINE") {
				$valid = $landlineArray;
			}

			// Get the second digit from the number
			$secondDi = (int)substr($thisNumber, 1, 1);
			
			// Check that the phone number is in the range we require
			if ( in_array($secondDi, $valid) ) {
			
				// Run the function to check if it's a valid UK number
				if (!$this->checkUKTelephone ($thisNumber, $errorNo, $errorText) ) {
					return FALSE;
				} else {
					// If the number is valid then strip the 0 again and return it
					if (strlen($thisNumber) > 9) {
						if ( substr($thisNumber, 0, 1) == "0" ) {
							$thisNumber = substr($thisNumber, 1);
						}
						return (int)$thisNumber;
					} else {
						return FALSE;
					}
				}
			} else {
				return FALSE;
			}
		}
		
		/**
		 * Pull the Alternate number from the CSV file.
		 * 
		 * @access public
		 * @param mixed $row
		 * @param mixed $mobileOnly (default: FALSE)
		 * @return void
		 */
		public function getAltNumber($row, $mobileOnly=FALSE)
		{
			if ($this->_numberColumns[1] != "x") {
				$altNumber = $this->checkNumberValid($this->_data[$row][$this->_numberColumns[1]], $mobileOnly);
				
				if ($altNumber != FALSE AND $this->getMainNumber($row, $mobileOnly) != $altNumber ) {
					return (int)$altNumber;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}
		
		
		/**
		 * Pull the Main number from the CSV file.
		 * 
		 * @access public
		 * @param mixed $row
		 * @param mixed $mobileOnly (default: FALSE)
		 * @return void
		 */
		public function getMainNumber($row, $mobileOnly=FALSE)
		{
			$mainNumber = $this->checkNumberValid($this->_data[$row][$this->_numberColumns[0]], $mobileOnly);
			
			if ( $mainNumber != FALSE ) {
				return (int)$mainNumber;

			} else {
				$mainNumber = $this->checkNumberValid($this->_data[$row][$this->_numberColumns[1]], $mobileOnly);
				if ( $mainNumber != FALSE ) {
					return (int)$mainNumber;
				} else {
					return FALSE;
				}
			}
		}
		
		
		/**
		 * Pull the First Name from the CSV file.
		 * 
		 * @access public
		 * @param mixed $row
		 * @return void
		 */
		public function getFirstName($row) {
		
			$firstName = $this->getColumn($row,$this->_nameColumns['fname']);
			if ( !empty($firstName) ) {
				return $firstName;
			} else {
				return FALSE;
			}
				
		}
		
		
		/**
		 * Pull the Surname from the CSV file.
		 * 
		 * @access public
		 * @param mixed $row
		 * @return void
		 */
		public function getSurname($row) {
			if ($this->_nameColumns['sname'] != "x") {
				$surname = $this->getColumn($row,$this->_nameColumns['sname']);
				if ( !empty($surname) ) {
					return $surname;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}		
		}
		
		function getColumn($row, $col) {
			return $this->_data[$row][$col];
		}
		
		// Set the columns the numbers are stored in.
		function setNumberColumns( $cols )
		{
			if ($this->_dataLoaded) {
				$this->_numberColumns = $cols;
			} else {
				return FALSE;
			}
			
		}
		
		function setNameColumns($fname, $sname) {
			if ($this->_dataLoaded) {
				$this->_nameColumns = array("fname"=>$fname, "sname"=>$sname);
			} else {
				return FALSE;
			}
		}
		
		
		function setHeadings($newHeadings) {
			// If we change the headings then we presume that row 1 is data
			
			array_unshift($this->_data, $newHeadings);
			
		}
		
		
		function getFileHeadings() {
			$headings = Array();
			
			return $this->_data[0];
			
		}
		
		
		function countRows(){
			return $this->numberOfRows;
		}
		
		// Import a CSV from a given database ID
		function load($id) {
			
			$fd = fopen ($id, "r");
			$contents = fread ($fd, filesize($id));
			fclose ($fd);
			
			$this->_dataLoaded = TRUE;
			
			$c = 0;
			foreach(explode("\r\n", $contents) as $line) {
				$this->_data[] = explode(",", $line);
				$c++;
			}
			
			$this->numberOfRows = $c;
			
			return TRUE;
						
		}
		
		// Construct function
		function __construct() {
			$this->_dataLoaded = FALSE;
		}
		
		
	}



?>

