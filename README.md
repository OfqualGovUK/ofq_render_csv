# ofq\_render\_csv

A Wordpress plugin to render a local CSV file as a table using a shortcode.

## Usage

    [csv src="/path/to/local_file.csv" disable="header" id="css-hook"]

## Version

Version 2.1
Wednesday 16th July 2014

## Licensing

Crown Copyright 2014.

This software is licensed under the MIT Licence. For more details see 'LICENCE'.

## Installation

Copy the file `ofq_render_csv.php` to `wp-content/plugins/ofq_render_csv/` in your wordpress install and then enable it from the admin interface.

## Changes

### v2.1

* Fixed a bug with handling of source file locations that were relative or absolute and not fully-formed URLs. The bug would cause the file to not be found.
* Added a class of `empty-top-left-cell` when a blank cell is inserted in the top-left. That is, when displaying both column and row headers.

### v2.0

* The option `disable="headers"` is deprecated and will be removed in a future version. Use `disable="CSVColHeaders"` instead.
* The option `headers` is deprecated and will be removed in a future version. Use `colheaders` instead.
* Added option `enable` that accepts a comma-separated list of features to explicitly enable.
* Added features `CSVColHeaders` and `CSVRowHeaders` to options `enable=` and `disable=`.
* Added option `colheaders` to add user-supplied headers to columns.
* Added option `rowheaders` to add user supplied headers to rows.
* Added option `linktosource` to add a link to the source CSV file.
* Added option `tableclass` to add a class attribute to the `<table>` tag.
* Newlines now preserved in the output by replacing them with `<br>` tags.
* Fixed a bug where the filename would be missing the leading / if a URI was given as the CSV source.
* Fixed a bug that missed the opening `<tr>` after the `<thead>` tag.

## Notes

* Newlines are preserved from the source CSV file by converting newlines into `<br>` tags.
* Empty cells are populated with a non-breaking space: `&nbsp;`.

## Options

### src
This is the source to the file. It can either be a path to the file, such as `/path/to/file.csv` or it can be a canonical URI such as `http://ofqual.gov.uk/path/to/file.csv`.

    [csv src="/path/to/file.csv"]
    [csv src="https://my.hostname.com/path/to/file.csv"]        

At present this plugin does not allow you to render CSV files that are hosted anywhere other than on the host this plugin is installed on. It will only render local CSV files. This is to prevent you using someone else's CSV file and it then being edited to display something profane on your site.

If you do want to display remote CSV files, it should be relatively trivial to adapt the code to allow this. Just look for the comment:

    // We will not accept if the file is on a remote host

The format of the file isn't checked and it doesn't care if the file ends in .csv or not.

### enable / disable

This option explicitly enables options that would normally be disabled by default, or disable options that are enabled by default.

    [csv src="/path/to/file.csv" enable="Option1,Option2" disable="Option3,Option4"]

The options are not case sensitive and are:

* CSVColHeaders [default=enabled]

  Enable this option to indicate that the first row of the CSV file contains headings for the data columns.
  
  Disable this option to indicate that the first row of the CSV file is data.
  
  Also see the table under `rowheaders` to see how this element reacts in conjunction with other options.

* CSVRowHeaders [default=disabled]

  Enable this option to indicate that the first cell in every row is a heading for the row. This makes the first data element of any row be displayed as a header with the `scope="row"` attribute added to the `<th>` element.
  
  Disable this option to indicate that the first cell in every row is data. The `CSVColHeaders` option overrides this setting, so if you disable `CSVRowHeaders` and enable `CSVColHeaders` The first cell of row 1 will still be treated as a header for the column.
  
  Also see the table under `rowheaders` to see how this element reacts in conjunction with other options.

* header (deprecated)

  This option is deprecated and has been replaced with `CSVColHeaders"` above.


### colheaders

This option allows you to override any header row in the source file or to provide column headers if none exist otherwise. It accepts a comma-separated list of headers. No checking is done to see if the number of cells match the CSV source.

    [csv src="/path/to/file.csv" colheaders="alpha,beta,gamma,delta,epsilon"]

The effect is dependent on the settings of other options. See the table under `rowheaders` for details.

### headers (deprecated)

This option is deprecated and has been replaced with the `colheaders` option above.

### rowheaders

This option allows you to override any header column in the source file or to provide row headers if none exist otherwise. If there are fewer headers specified than rows in the source CSV file, then the header cell for those rows will be left blank. To insert a blank header use `,,` - for example `one,two,,four,,,seven`.

    [csv file="/path/to/file.csv" rowheaders="One,Two,Three,Four,,Six"]

The effect of this option depends on the other settings, such as `enable="CSVColHeaders,CSVRowHeaders"`, `disable="CSVColHeaders,CSVRowHeaders"` and `colheaders="..."`. The table below shows the effects of these different options.

    | CSVColHeaders | colheaders="..." | CSVRowHeaders | rowheaders="..." | Result                                                                                                                 |
    |:-------------:|:----------------:|:-------------:|:----------------:| ---------------------------------------------------------------------------------------------------------------------- |
    | Disabled      | No               | Disabled      | No               | No column headers. No row headers. Every cell rendered as data. Data starts at row 1 col 1.                            |
    | Enabled       | No               | Disabled      | No               | Column headers from row 1. No row headers. Data starts at row 2 col 1.                                                 |
    | Disabled      | Yes              | Disabled      | No               | Column headers from user. No row headers. Data starts at row 1 col 1.                                                  |
    | Enabled       | Yes              | Disabled      | No               | Column headers from user. No row headers. First row from CSV discarded. Data starts at row 2 col 1.                    |
    | Disabled      | No               | Enabled       | No               | No column headers. Row headers from col 1. Data starts at row 1 col 2.                                                 |
    | Enabled       | No               | Enabled       | No               | Column headers from row 1. Row headers from col 1. Data starts at row 2 col 2.                                         |
    | Disabled      | Yes              | Enabled       | No               | Column headers from user. Row headers from col 1. Data starts at row 1 col 2.                                          |
    | Enabled       | Yes              | Enabled       | No               | Column headers from user. Row headers from col 1. Row 1 discarded. Data starts row 2 col 2.                            |
    | Disabled      | No               | Disabled      | Yes              | No column headers. User-overridden row headers. Data starts at row 1 col 1.                                            |
    | Enabled       | No               | Disabled      | Yes              | Column headers from row 1, user-overridden row headers. Data starts at row 2 col 1.                                    |
    | Disabled      | Yes              | Disabled      | Yes              | Column headers from user. Row headers from user. Data starts at row 1 col 1.                                           |
    | Enabled       | Yes              | Disabled      | Yes              | Column headers from user. Row headers from user. CSV row 1 discarded. Data starts at row 2 col 1.                      |
    | Disabled      | No               | Enabled       | Yes              | No column headers. User-overridden row headers. First column discarded. Data starts at row 1 col 2.                    |
    | Enabled       | No               | Enabled       | Yes              | Column headers from line 1. Row headers from user. First column discarded. Data starts row 2 col 2.                    |
    | Disabled      | Yes              | Enabled       | Yes              | Column headers from user. Row headers from user. First column discarded. Data starts row 1 col 2.                      |
    | Enabled       | Yes              | Enabled       | Yes              | Column headers from user. Row headers from user. First row discarded. First column discarded. Data starts row 2 col 2. |

### id

Allows you to set an id attribute on the table element, for CSS or Javascript hooks.

    [csv src="/path/to/file.csv" id="table-a"]

### caption

Provides a table caption.

    [csv src="/path/to/file.csv" caption="Table A: showing something awesome"]

### summary

Allows you to provide an accessible text summary of the table.

    [csv src="/path/to/file.csv" summary="This description is read out by screen readers to give an overview description of what this table shows without having to have all the data read out cell by cell."]

### linktosource

Allows you to link to the source CSV file, and specify the text for the link.

    [csv src="/path/to/file.csv" linktosource="Source data file"]

This will produce the code `<p><a href="/path/to/file.csv" class="tabledatasource">Source data file</a></p>`

### tableclass

Add a class attribute to the `<table>` tag.

    [csv src="/path/to/file.csv" tableclass="sortable"]

This will produce the code `<table class="sortable">`
