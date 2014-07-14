<?php
/**
 * Plugin Name: Ofqual Render CSV
 * Plugin URI: https://github.com/icerunner/ofq_render_csv
 * Description: Import data from a CSV spreadsheet and display in a table.
 * Version: 1.0
 * Author: Philip McAllister
 * Author URI: http://about.me/icerunner
 * Text Domain: ofq_render_csv
 * License: MIT
 */
function csv_shortcode($attrs, $content = null) {
	
	$plug_debug = true;
	$header_row = [];
	$disable = [];
	$table_output = "<table";
	$csv_line = [];
	
	
	// XXX DEBUG
	//if ($plug_debug) { print "\n\n<!-- CSV: Attributes\n".print_r($attrs)."\n-->\n\n"; }
	if ($plug_debug) { echo "<!-- CSV: Server is ".$_SERVER['SERVER_NAME']." -->\n"; }
	
	// Only process is there is a file src set
	if (isset($attrs['src']))
	{
		// If the src starts with a protocol, deal with it here
		if (preg_match('/^https?:\/\/(.+?)\/(.+)/',$attrs['src'],$match))
		{
			// We will accept if the file is on one of our hosts
			if ($match[1] == $_SERVER['SERVER_NAME'])
			{
				$attrs['src'] = $match[2];
			}
			// We will not accept if the file is on an remote host
			else
			{
				return "<!-- CSV: Rendering of remote CSV files is not allowed. Our host is: '".$_SERVER['SERVER_NAME']."' src was: '".$attrs['src']."' -->";
			}
		}
			
		
		// Find the local file path of the referenced file
		$local_csv_path = realpath(ABSPATH . $attrs['src']);
		
		// XXX DEBUG
		if ($plug_debug) { print("<!-- Local CSV file: '".$local_csv_path."' -->\n"); }
		
		// Open CSV file	
		$csv_fh = fopen($local_csv_path,"r");
		
		// If file successfully opened for reading
		if ($csv_fh)
		{
			// Split out 'disable' attribute
			if (array_key_exists('disable',$attrs))
			{
				$disable = explode(',',$attrs['disable']);
			}
			// Split out the headers
			if (array_key_exists('headers',$attrs))
			{
				$header_row = explode(',',$attrs['headers']);
			}
				
			// Add a table ID if one specified
			if (array_key_exists('id',$attrs))
			{
				$table_output .= ' id="'. $attrs['id'] .'"';
			}
				
			// Add a table summary if one specified
			if (array_key_exists('summary',$attrs))
			{
				$table_output .= ' summary="'. $attrs['summary'] .'"';
			}
				
			$table_output .= ">";
				
			// If a caption is provided add it
			if (array_key_exists('caption',$attrs))
			{
				$table_output .= "<caption>" . $attrs['caption'] . "</caption>";
			}
			
			// Logic to control how headers appear
			// +---------------------+-------------------+---------------------------------------------+
			// | Header row disabled | Headers overidden |                   Result                    |
			// +---------------------+-------------------+---------------------------------------------+
			// |         No          |        No         | Use row 1 as headers. Data starts at row 2. |
			// +---------------------+-------------------+---------------------------------------------+
			// |         Yes         |        No         | No headers. Data starts at row 1.           |
			// +---------------------+-------------------+---------------------------------------------+
			// |         No          |        Yes        | Use override headers from post or page.     |
			// |                     |                   | Row 1 discarded. Data starts at row 2.      |
			// +---------------------+-------------------+---------------------------------------------+
			// |         Yes         |        Yes        | Use override headers. Data starts at row 1. |
			// +---------------------+-------------------+---------------------------------------------+

			// XXX DEBUG
			//if ($plug_debug) { echo "CSV: Header debug info \n".print_r($attrs)."\n".print_r($attrs['disable'])."\n".print_r($headers)."\n-->\n"; }

			if ((!array_key_exists('disable',$attrs) || !in_array('header',$disable)) && !array_key_exists('headers',$attrs)) {  // Header row not disabled and no override headers
				// Get header row from file
				$header_row = fgetcsv($csv_fh);
				// XXX DEBUG
				if ($plug_debug) { echo "\n<!-- CSV: Header not disabled, no override -->\n"; }
			}
			elseif (in_array('header',$disable) && !array_key_exists('headers',$attrs)) { // Header row disabled and no override headers
				// Do nothing because no headers used
				// XXX DEBUG
				if ($plug_debug) { echo "\n<!-- CSV: Header disabled, no override -->\n"; }
			}
			elseif ((!array_key_exists('disable',$attrs) || !in_array('header',$disable)) && array_key_exists('headers',$attrs)) { // Header row disabled and override headers provided
				// Discard the first row from the file
				$discard_row = fgetcsv($csv_fh);
				// XXX DEBUG
				if ($plug_debug) { echo "\n<!-- CSV: Header not disabled, override headers -->\n"; }
			}
			elseif (in_array('header',$disable) && array_key_exists('headers',$attrs)) {
				// Do nothing because override headers set above
				// XXX DEBUG
				if ($plug_debug) { echo "\n<!-- CSV: Header disabled, override headers -->\n"; }
			}
				
			// If headers set to be used, display them
			if (count($header_row) > 0)
			{	
				$table_output .= "\n<thead>";
				
				foreach ($header_row as $cell)
				{
					$table_output .= "<th>".$cell."</th>\n";
				}
				$table_output .= "</tr>\n</thead>\n";
			}
				
			// Start the table body
			$table_output .= "<tbody>\n";
			// Parse CSV file and render as rows & cells
			while (($csv_line = fgetcsv($csv_fh)) !== FALSE)
			{
				$table_output .= "<tr>\n";
				foreach ($csv_line as $cell)
				{
					//replace empty cell with nonbreaking space
					if ( !trim($cell) ) {
 						 $cell="&nbsp;"; 						 
					}
					$table_output .= "<td>".$cell."</td>\n";
				}
				$table_output .= "</tr>\n";
			}
			$table_output .= "</tbody></table>\n";
				
			fclose($csv_fh);
				
  		return $table_output;
  	}
  	else
  	{
  		return "<!-- CSV: Couldn't open file '".$local_csv_path."' couldn't be opened for reading: ".error_get_last()."-->";
  	}
  }
  else
  {
  	return '<!-- CSV: No CSV file specified to render -->';
  }
}

add_shortcode('csv', 'csv_shortcode');