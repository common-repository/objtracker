<?php
/**
 * Page presents index of changes to the scorecard.
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
function bs_auditindex( $bsdriver, $bsuser )
{

	$C_Who      = $_GET['Who'];
	$C_What     = $_GET['What'];
	$configPage = new BsAuditIndexPage(
		$bsdriver,
		$bsuser,
		__( 'Updates by User' ),	// Parm: Title
		array(				// Parm: Gridview columns
			new ObjtrackerGvColumn( __( 'Action' ), 'C_Action' ),
			new ObjtrackerGvColumn( __( 'Change' ), 'C_ID' ),
			new ObjtrackerGvColumn( __( 'Date' ), 'C_Track_Date' ),
			new ObjtrackerGvColumn( __( 'By' ), 'C_Track_UserID' ),
			new ObjtrackerGvColumn( __( 'Table' ), 'C_TableName' ),
			new ObjtrackerGvColumn( __( 'Description' ), 'C_Documentation' ),
			),
		'P_Audit_IndexList',
		'( %d, %d, %s, %s, %s )',
		array( $bsuser->OrgID, $bsuser->ID, $C_Who, $C_What, $bsuser->UserName ),
		'?'
		);
	return $configPage->Response();
}

/**
 * Audit index processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsAuditIndexPage extends ObjtrackerConfigPage
{
	const PARM_WHO  = 2;
	const PARM_WHAT = 3;

	/**
	 * rows found
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	private $rows;

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

		$bsdriver->PageState = '&Who=' . $this->DbProcParms[self::PARM_WHO] . '&What=' . $this->DbProcParms[self::PARM_WHAT];

		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text( __( 'The table below lists a subset of changes made.' ) );
		$this->description_list_start();
		$this->description_list_item(
			__(
				'To sort the table, click on <b>Action</b>, <b>Change</b>, <b>Date</b>, <b>By</b>, or <b>Table</b>.'
				)
			);
		if ( $bsuser->is_admin() ) {
			$this->description_list_item( __( 'To view the list of changes made, click on Change.' ) );
		}
		$this->description_list_end();
		$this->description_end();
		$this->_Description .= $bsdriver->title_heading( __( 'Updates' ) );
	}


	/**
	 * Retrieve objective from database.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function trailer()
	{
		return $this->RowCount > 0 ? __( 'Count=' ) . $this->RowCount : __( 'No updates found' );
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

		$C_ID            = $row[$bsdriver->Field->C_ID];
		$C_Action        = $row[$bsdriver->Field->C_Action];
		$C_Name          = $row[$bsdriver->Field->C_Name];
		$C_Track_Date    = $row[$bsdriver->Field->C_Track_Date];
		$C_Track_UserID  = $row[$bsdriver->Field->C_Track_UserID];
		$C_TableName     = $row[$bsdriver->Field->C_TableName];
		$C_Documentation = $row[$bsdriver->Field->C_Documentation];

		return ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Action ) )
				. ObjtrackerEasyStatic::table_td(
					'', 
					"<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm
					. 'sc_menu=AuditItem&SKey=' . $C_ID . "'>" . $C_Name . '</a>'
					)
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Date ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_UserID ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_TableName ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Documentation ) );
	}
}

?>
