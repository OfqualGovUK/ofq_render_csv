<?php
/**
 * Plugin Name: Ofqual Render CSV
 * Plugin URI: https://github.com/icerunner/ofq_render_csv
 * Description: Import data from a CSV spreadsheet and display in a table.
 * Version: 2.1
 * Author: Philip McAllister
 * Author URI: http://about.me/icerunner
 * Text Domain: ofq_render_csv
 * License: MIT
 */
function csv_shortcode($attrs, $content = null) {
	
	$plug_debug = false;
	$csv_file = "";
	$headers_for_cols = [];
	$headers_for_rows = [];
	$user_row_headers = false;
	$enable = [];
	$disable = [];
	$table_output = "<table";
	$csv_line = [];
	$col_num = 0;
	$file_error = "";
	
	// XXX DEBUG
	//if ($plug_debug) { print "\n\n<!-- CSV: Attributes\n".print_r($attrs)."\n-->\n\n"; }
	if ($plug_debug) { echo "<!-- CSV: Server is ".$_SERVER['SERVER_NAME']." -->\n"; }
	
	// Only process is there is a file src set
	if (isset($attrs['src']))
	{
		// If the src starts with a protocol, deal with it here
		if (preg_match('/^https?:\/\/(.+?)(\/.+)/',$attrs['src'],$match))
		{
			// We will accept if the file is on one of our hosts
			if ($match[1] == $_SERVER['SERVER_NAME'])
			{
				$attrs['src'] = $match[2];
				
				// Find the local file path of the referenced file
				$csv_file = realpath(ABSPATH . $attrs['src']);
			}
			// We will not accept if the file is on an remote host
			else
			{
				return "<!-- CSV: Rendering of remote CSV files is not allowed. Our host is: '".$_SERVER['SERVER_NAME']."' src was: '".$attrs['src']."' -->";
			}
		}
		// Else file is a relative or absolute path but not a fully-formed URI
		else
		{
			$csv_file = realpath(ABSPATH . $attrs['src']);
		}
		// XXX DEBUG
		if ($plug_debug) { print("<!-- CSV: File is: '".$csv_file."' -->\n"); }
		
		// Open CSV file
		$csv_fh = fopen($csv_file,"r");
		
		// If file successfully opened for reading
		if ($csv_fh)
		{
			// Split out 'enable' attribute
			if (array_key_exists('enable',$attrs))
			{
				$enable = explode(',',$attrs['enable']);
				// Put enables features in the array as lowercase keys
				$enable = array_combine(array_map('strtolower', $enable), $enable);
			}
			
			// Split out 'disable' attribute
			if (array_key_exists('disable',$attrs))
			{
				$disable = explode(',',$attrs['disable']);
				// Put enables features in the array as lowercase keys
				$disable = array_combine(array_map('strtolower', $disable), $disable);
				
				// XXX DEPRECATED
				// Deal with deprecated option
				if (array_key_exists('header',$disable))
				{
					$disable['csvcolheaders'] = 'csvcolheaders';
				}
			}
			
			// XXX DEPRECATED
			// Split out the headers - replaced by colheaders
			if (array_key_exists('headers',$attrs))
			{
				if (!array_key_exists('colheaders',$attrs))
				{
					$attrs['colheaders'] = $attrs['headers'];
				}
			}
			
			// Split out the column headers - takes precedence over 'headers' if both exist
			if (array_key_exists('colheaders',$attrs))
			{
				$headers_for_cols = explode(',',$attrs['colheaders']);
			}
			
			// Split out the row headers
			if (array_key_exists('rowheaders',$attrs))
			{
				$headers_for_rows = explode(',',$attrs['rowheaders']);
				$user_row_headers = true;
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
			
			
			// Add a class to the table tag if one specified
			if(array_key_exists('tableclass',$attrs))
			{
				$table_output .= ' class="'. $attrs['tableclass'] .'"';
			}
			$table_output .= ">";
				
			// If a caption is provided add it
			if (array_key_exists('caption',$attrs))
			{
				$table_output .= "<caption>" . $attrs['caption'] . "</caption>";
			}
			
			// Logic to control how headers appear
			//
            // For details of the various combinations that affect how headers appear see https://github.com/icerunner/ofq_render_csv/blob/master/README.md
            
			// XXX DEBUG
			//if ($plug_debug) { echo "CSV: Header debug info \n".print_r($attrs)."\n".print_r($disable)."\n-->\n"; }

			/*
			 * LOGIC TO CONTROL COLUMN HEADERS
			 */
			if ((!array_key_exists('disable',$attrs) || !array_key_exists('csvcolheaders',$disable)) && !array_key_exists('colheaders',$attrs))  // Header row not disabled and no override headers
			{
				// Get header row from file
				$headers_for_cols = fgetcsv($csv_fh);
				// If row headers also enabled, knock out the first header
				if (array_key_exists('csvrowheaders',$enable))
				{
					array_shift($headers_for_cols);
				}
				// XXX DEBUG
				if ($plug_debug) { echo "\n<!-- CSV: Header not disabled, no override -->\n"; }
			}
			elseif (array_key_exists('csvcolheaders',$disable) && !array_key_exists('colheaders',$attrs)) // Header row disabled and no override headers
			{
				// Do nothing because no headers used
				// XXX DEBUG
				if ($plug_debug) { echo "\n<!-- CSV: Header disabled, no override -->\n"; }
			}
			elseif((!array_key_exists('disable',$attrs) || array_key_exists('csvcolheaders',$enable) || !array_key_exists('csvcolheaders',$disable)) && array_key_exists('colheaders',$attrs)) // header row not disabled, no override headers
			{
				// Discard the first row from the file
				$discard_row = fgetcsv($csv_fh);
				// XXX DEBUG
				if ($plug_debug) { echo "\n<!-- CSV: Header not disabled, override headers -->\n"; }
			}
			elseif (array_key_exists('csvcolheaders',$disable) && array_key_exists('colheaders',$attrs)) { // Header row disabled and override headers
				// Do nothing because override headers set above
				// XXX DEBUG
				if ($plug_debug) { echo "\n<!-- CSV: Header disabled, override headers -->\n"; }
			}
			
			
			/*
			 * LOGIC TO CONTROL ROW HEADERS
			 */
			
			
			// If column headers set to be used, display them
			if (count($headers_for_cols) > 0)
			{	
				
				$table_output .= "\n<thead><tr>";
				
				// If we also have row headers, insert a blank cell to go in the top-left
				if (array_key_exists('rowheaders',$attrs) || (array_key_exists('csvrowheaders',$enable) && !array_key_exists('csvrowheaders',$disable)))
				{
					$table_output .= "<td class=\"empty-top-left-cell\">&nbsp;</td>\n";
				}
				
				foreach ($headers_for_cols as $cell)
				{
					$table_output .= "<th>".nl2br($cell)."</th>\n";
				}
				$table_output .= "</tr>\n</thead>\n";
			}
				
			// Start the table body
			$table_output .= "<tbody>\n";
			// Parse CSV file and render as rows & cells
			while (($csv_line = fgetcsv($csv_fh)) !== FALSE)
			{
				$col_num = 0;
				$table_output .= "<tr>\n";
				foreach ($csv_line as $cell)
				{
					//replace empty cell with nonbreaking space
					if ( !trim($cell) )
					{
 						 $cell="&nbsp;"; 						 
					}
					// If user-set row headers
					if (!$col_num && $user_row_headers ) 
					{
						$th = array_shift($headers_for_rows);
						
						if ( !trim($th) || !isset($th))
						{
 						 	$th="&nbsp;"; 						 
						}
						var_dump($th);
						
						$table_output .= "<th scope=\"row\">".nl2br($th)."</th>";
						
						// If we're not discarding the first column, output it
						if (!array_key_exists('csvrowheaders',$enable))
						{
							$table_output .= "<td>".nl2br($cell)."</td>";
						}
					}
					// If not user-set row headers, but using headers from CSV column 1
					elseif (!$col_num && array_key_exists('csvrowheaders',$enable))
					{
						$table_output .= "<th scope=\"row\">".nl2br($cell)."</th>\n";
					}
					// Otherwise this is just a normal data cell
					else
					{
						$table_output .= "<td>".nl2br($cell)."</td>\n";
					}

					$col_num++;
				}
				$table_output .= "</tr>\n";
			}
			fclose($csv_fh);
			
			$table_output .= "</tbody></table>\n";
			
			if (array_key_exists('linktosource',$attrs))
			{
				if (!trim($attrs['linktosource']) || !isset($attrs['linktosource']))
				{
					$attrs['linktosource'] = "Source data file";
				}
				$table_output .= "<p><a href=\"".$attrs['src']."\" class=\"tabledatasource\">".$attrs['linktosource']."</a></p>\n";
			}
			
				
  		return $table_output;
  	}
  	else
  	{
  		$file_error = error_get_last();
  		return "<!-- CSV: Couldn't open file '".$csv_file."' couldn't be opened for reading: ".$file_error['message']." -->";
  	}
  }
  else
  {
  	return '<!-- CSV: No CSV file specified to render -->';
  }
}

add_shortcode('csv', 'csv_shortcode');