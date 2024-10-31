<?php
/**
 * Class manages header row of table gridview, facilitating sort by column of the table.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Class associates gridview header titles with database column names.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerGvColumn
{
	/**
	 * Title for gridview header.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Title;

	/**
	 * Database column name.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $DbColumnName;

	/**
	 * Constructor for ObjtrackerGvColumn
	 *
	 * @since    1.0
	 * @param    string    $title         Title to show in gridview header
	 * @param    string    $dbColumnName  Database name of column
	 * @return   void
	 */
	function __construct( $title, $dbColumnName )
	{
		if ( $dbColumnName != null && $dbColumnName != '' )
			$this->DbColumnName = $dbColumnName;
		$this->Title = $title;
	}
}
?>
