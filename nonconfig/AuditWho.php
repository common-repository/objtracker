<?php
/**
 * Page presents an index by user of who has modified the scorecard.
 *
 *	 Requirements:
 *		o Only admins can update
 *		o Names of must be unique, stored proc checks
 *		o Link to list of objectives that match
 *		o Sortable column headers
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */
/**
 * Returns this page unique html text.
 *
 * @since    1.0
 * @param    object  $bsdriver   The environment object
 * @param    object  $bsuser     The user object
 * @return   string              Page's unique html text.
 */
function bs_auditwho( $bsdriver, $bsuser )
{
	$configPage = new BsAuditWhoPage(
		$bsdriver,
		$bsuser,
		__( 'Updates by User' ),	// Parm: Title
		array(				// Parm: Gridview columns
				new ObjtrackerGvColumn( __( 'By' ), 'C_ID' ),
				new ObjtrackerGvColumn( __( 'Count' ), 'C_TheCount' ),
			),
		'P_Audit_IndexList',
		'( %d, %d, %s, %s, %s )',
		array( $bsuser->OrgID, $bsuser->ID, 'Who', 'None', $bsuser->UserName ),
		''
		);
	return $configPage->Response();
}

/**
 * Audit who processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsAuditWhoPage extends ObjtrackerConfigPage
{
	/**
	 * Explain now to use page by updating class variable.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function description()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text( __( 'The table below lists each user who has made a change and the count of the changes.' ) );
		$this->description_list_start();
		$this->description_list_item( __( 'To sort the table, click on <b>By</b> or <b>Count</b>.' ) );
		if ( $bsuser->is_admin() ) {
			$this->description_list_item( __( "To view the list of changes made by that user, click on the user's ID." ) );
		}
		$this->description_list_end();
		$this->description_end();
		$this->_Description .= $bsdriver->title_heading( __( 'Updates by User' ) );
	}


	/**
	 * Retrieve objective from database.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function trailer()
	{
		return '';
	}
	/**
	 * Validate user input and insert row into database table.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_insert()
	{
		return '';
	}

	/**
	 * Validate user input and update a row in database table.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_update()
	{
		return '';
	}

	/**
	 * Validate user and delete row from database table.
	 *
	 * Beware that, the stored procedure may also reject the delete!
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_delete()
	{
		return '';
	}

	/**
	 * Return a row of a gridview that user click edit on.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_edit_this()
	{
		return '';
	}

	/**
	 * Return a row of a gridview that user click update on but had an error.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_update_this()
	{
		return '';
	}

	/**
	 * Return a row of a gridview other than user click update or add.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_other()
	{
		return '';
	}

	/**
	 * Return a footer row of a gridview when there was an error in prior add.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_footer_error()
	{
		return '';
	}

	/**
	 * Return a footer row of a gridview.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_footer()
	{
		return '';
	}

	/**
	 * Return a row of a gridview that is only being displayed.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_list( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_TheCount = $row[$bsdriver->Field->C_TheCount];
		$C_ID       = $row[$bsdriver->Field->C_ID];

		return	ObjtrackerEasyStatic::table_td(
			'',
			"<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm
				. 'sc_menu=AuditIndex&Who=Who&What=' . $C_ID . "'>" . $C_ID . '</a>'
			)
			. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_TheCount )	);
	}
}

?>
