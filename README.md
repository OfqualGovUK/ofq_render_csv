# ofq\_render\_csv

A Wordpress plugin to render a local CSV file as a table using a shortcode.

## Usage

    [csv src="/path/to/local_file.csv" disable="header" id="css-hook"]

## Licensing

Copyright (c) Philip McAllister 2014.

This software is licensed under the MIT Licence. For more details see 'LICENCE'.

## Options

### src
This is the source to the file. It can either be a path to the file, such as `/path/to/file.csv` or it can be a canonical URI such as `http://ofqual.gov.uk/path/to/file.csv`.

    [csv src="/path/to/file.csv"]
    [csv src="https://my.hostname.com/path/to/file.csv"]        

At present this plugin does not allow you to render CSV files that are hosted anywhere other than on the host this plugin is installed on. It will only render local CSV files. This is to prevent you using someone else's CSV file and it then being edited to display something profane on your site.

If you do want to display remote CSV files, it should be relatively trivial to adapt the code to allow this. Just look for the comment:

    // We will not accept if the file is on a remote host

The format of the file isn't checked and it doesn't care if the file ends in .csv or not.

### disable

This option accepts a comma-separated list of features to disable.

            [csv src="/path/to/file.csv" disable="option1,option2"]

At the moment the options are:

* header

  To stop the first row being used as a table header.
 
See also the table under 'headers' to see how this option behaves in conjunction with the 'headers' option.

### headers
This option allows you to override any header row in the source file or to provide headers if none exist otherwise. It accepts a comma-separated list of headers. No checking is done to see if the number of cells match the CSV source.

        [csv src="/path/to/file.csv" headers="alpha,beta,gamma,delta,epsilon"]

The effect is dependent on whether you have disabled the header row using the 'disabled' option. The table below shows how the two interact together.

    | disabled="header" | headers="..." | Result                                                                                           |
    |:-----------------:|--------------:| ------------------------------------------------------------------------------------------------ |
    | No                | No            | Row 1 from the CSV file is used for the table headers. Data starts at CSV row 2                  |
    | Yes               | No            | No headers in the table. Data starts at CSV row 1                                                |
    | No                | Yes           | Use the override headers for the table headers. CSV row 1 is discarded. Data starts at CSV row 2 |
    | Yes               | Yes           | Use the override headers for the table headers. Data starts at CSV row 1                         |


### id

Allows you to set an id attribute on the table element, for CSS or Javascript hooks.

    [csv src="/path/to/file.csv" id="table-a"]

### caption

Provides a table caption.

    [csv src="/path/to/file.csv" caption="Table A: showing something awesome"]

### summary

Allows you to provide an accessible text summary of the table.

    [csv src="/path/to/file.csv" summary="This description is read out by screen readers to give an overview description of what this table shows without having to have all the data read out cell by cell."]