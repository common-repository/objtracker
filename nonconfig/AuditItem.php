<?php
/**
 * Page shows the before and after values of a change to the scorecard.
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
function bs_audititem( $bsdriver, $bsuser )
{
	$C_SKey     = $_GET['SKey'];
	$configPage = new BsAuditItemPage(
		$bsdriver,
		$bsuser,
		__( 'Updates' ),		// Parm: Title
		array(			// Parm: Gridview columns
			new ObjtrackerGvColumn( __( 'Field' ), 'C_ID' ),
			new ObjtrackerGvColumn( __( 'Before' ), 'C_Value1' ),
			new ObjtrackerGvColumn( __( 'After' ), 'C_Value2' ),
			new ObjtrackerGvColumn( __( 'Field description' ), 'C_Documentation' ),
			),
		'P_Audit_ItemList',
		'( %d, %d, %s )',
		array( $bsuser->OrgID, $bsuser->ID, $C_SKey ),
		'?'
		);
	return $configPage->Response();
}

/**
 * Audit item processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsAuditItemPage extends ObjtrackerConfigPage
{
	const PARM_KEY = 2;

	/**
	 * Holds the filtered measurements of all objectives
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $Pass;

	/**
	 * Returns this page unique html text.
	 *
	 * @since    1.0
	 * @return   string              Page's unique html text.
	 */
	public function response()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		// Setup
		if ( isset( $_GET[ 'SortHiddenField' ] ) ) {
			$this->HiddenSortField     = $_GET['SortHiddenField'];
			$this->HiddenSortDirection = $_GET['SortHiddenDirection'];
//			$bsdriver->trace_text( 'get hidden Sortfield ' . $this->HiddenSortDirection . ' ' . $this->HiddenSortField );
		} else {
			$this->HiddenSortField     = '' ;
			$this->HiddenSortDirection = '';
		}
//		$bsdriver->trace_text( 'hidden(' . $this->HiddenSortDirection . $this->HiddenSortField . ')' );

		$message = '';
		$this->ActionChar = substr( $bsdriver->Action, 0, 1 );
//		$bsdriver->trace_text( ' action(' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		switch ( $this->ActionChar ) {
			case self::BSPAGE_SORT: // User click to sort by column
				$lastSort = $_GET[ 'Ss' ];
				if ( strlen( $lastSort ) > 1 ) {
					$lastField     = substr( $lastSort, 2 );
					$lastDirection = substr( $lastSort, 0, 1 );

					if ( $lastField == $bsdriver->SortField ) {
						$bsdriver->trace_text( ' sort same' );
						$this->SortDirection = $lastDirection == 'A' ? 'D' : 'A';
					} else {
						$bsdriver->trace_text( ' sort new' );
						$this->SortDirection = 'A';
					}
				} else {
					$bsdriver->trace_text( ' sort 1st' );
					$this->SortDirection = 'A';
				}
				break;
			case self::BSPAGE_LIST:
				break;
			default: // Initial or no action
				break;
		}
		$prefix = $this->preface();
		$this->description();
		$prefix2 = $this->preface2();

		$this->GvResults = $bsdriver->platform_db_query( $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );
		$this->RowCount  = count( $this->GvResults );

		return
			$prefix
			. $this->_Description
			. $prefix2
			. $this->gridview1();
	}

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

		$bsdriver->PageState = '&SKey=' . $this->DbProcParms[self::PARM_KEY];

		$this->setpage_description_head( __( 'Selected Change' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text( __( 'The table below lists the fields changed.' ) );
		$this->description_end();

		$saveheaders     = $this->GvColumns;
		$this->GvColumns = array(
			new ObjtrackerGvColumn( __( 'Action' ), '' ),
			new ObjtrackerGvColumn( __( 'Change' ), '' ),
			new ObjtrackerGvColumn( __( 'Date' ), '' ),
			new ObjtrackerGvColumn( __( 'By' ), '' ),
			new ObjtrackerGvColumn( __( 'Table' ), '' ),
			new ObjtrackerGvColumn( __( 'Description' ), '' ),
			);

		$this->GvResults = $bsdriver->platform_db_query( 'P_Audit_IndexItem', $this->DbProcArgs, $this->DbProcParms );
		$this->Pass      = 1;

		$this->_Description .= $this->gridview1() . '<br />';
		
		$this->GvColumns = $saveheaders;
		$this->Pass      = 2;
		
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

		if ( $this->Pass == 1 ) {
			$C_ID            = $row[$bsdriver->Field->C_ID];
			$C_Action        = $row[$bsdriver->Field->C_Action];
			$C_Name          = $row[$bsdriver->Field->C_Name];
			$C_Track_Date    = $row[$bsdriver->Field->C_Track_Date];
			$C_Track_UserID  = $row[$bsdriver->Field->C_Track_UserID];
			$C_TableName     = $row[$bsdriver->Field->C_TableName];
			$C_Documentation = $row[$bsdriver->Field->C_Documentation];

			return
				ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Action ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Name ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Date ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_UserID ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_TableName ) )
				. ObjtrackerEasyStatic::table_td( '',  stripslashes( $C_Documentation ) );
		} else {
			$C_ID            = $row[$bsdriver->Field->C_ID];
			$C_Value1        = $row[$bsdriver->Field->C_Value1];
			$C_Value2        = $row[$bsdriver->Field->C_Value2];
			$C_Documentation = $row[$bsdriver->Field->C_Documentation];

			return
				ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Value1 ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Value2 ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Documentation ) );
		}
	}
}

?>
