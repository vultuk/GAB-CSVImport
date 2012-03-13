<?php

	class csv
	{
		
		var $_data;
		var $_dataLoaded;
		var $_numberColumns;
		
		
		function checkNumberValid($number)
		{
			
			preg_match('^\s*\(?(020[7,8]{1}\)?[ ]?[1-9]{1}[0-9{2}[ ]?[0-9]{4})|(0[1-8]{1}[0-9]{3}\)?[ ]?[1-9]{1}[0-9]{2}[ ]?[0-9]{3})\s^', $number, $matches);
			
			print_r($matches);
			
			if ( substr($number, 0, 1) == "0" ) {
				$number = substr($number, 1);
			}
			
			
			
			return $number;
		}
		
		function getAltNumber($row)
		{
			$altNumber = $this->checkNumberValid($this->_data[$row][$this->_numberColumns[1]]);
			
			if ($altNumber != FALSE && ( $this->getMainNumber($row) != $altNumber ) ) {
				return $altNumber;
			} else {
				return FALSE;
			}
		}
		
		function getMainNumber($row)
		{
			$mainNumber = $this->checkNumberValid($this->_data[$row][$this->_numberColumns[0]]);
			
			if ( $mainNumber != FALSE ) {
				return $mainNumber;
			} else {
				$mainNumber = $this->checkNumberValid($this->_data[$row][$this->_numberColumns[1]]);
				if ( $mainNumber != FALSE ) {
					return $mainNumber;
				} else {
					return FALSE;
				}
			}
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
		
		
		// Import a CSV from a given database ID
		function load($id) {
			
			$db = Database::connect();
			$results = $db->prepared_query(FALSE, 'SELECT file FROM files WHERE id=?;', "i", Array($id) );
			
			// If we've loaded data then we put it into the data
			if (count($results > 0)) {
				$this->_dataLoaded = TRUE;
				
				foreach(explode("\r\n", $results[0]['file']) as $line) {
					$this->_data[] = explode(",", $line);
				}
				
				return TRUE;
				
			} else {
				$this->_dataLoaded = FALSE;
				return FALSE;
			}
		}
		
		// Construct function
		function __construct() {
			$this->_dataLoaded = FALSE;
		}
		
		
	}



?>

