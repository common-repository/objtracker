<?php
/**
 * Administator's page for managing fiscal years.
 *
 * Requirements:
 *		o Only admins can add, update, or delete
 *		o Names of fiscal years must be unique, stored proc checks
 *		o Must not delete fiscal year with objectives
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
function bs_fiscalyears( $bsdriver, $bsuser )
{
	$configPage = new BsFiscalYearsPage(
		$bsdriver,
		$bsuser,
		__( 'Fiscal Years' ),		// Parm: Title
		array(				// Parm: Gridview columns
				new ObjtrackerGvColumn( __( 'FiscalYear' ), 'C_FormatedFiscalYear' ),
				new ObjtrackerGvColumn( __( 'Objectives' ), 'C_Usage' ),
				new ObjtrackerGvColumn( __( 'Title' ), 'C_FormatedFiscalYear' ),
				new ObjtrackerGvColumn( __( 'Changed' ), 'C_Track_Changed' ),
				new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
				new ObjtrackerGvColumn( '', '' ),
			),
		'P_FiscalYearList',
		'( %d, %d )',
		array( $bsuser->OrgID, $bsuser->ID ),
		__( 'Are you sure you want to delete this fiscal year?' )
		);
	return $configPage->Response();
}

/**
 * Fiscal year processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsFiscalYearsPage extends ObjtrackerConfigPage
{
	/**
	 * Count of fiscal years.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	private $FiscalYear_Count;

	/**
	 * Holds array of fiscal years that aren't in the database, but ready for dropdown box.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $MissingFiscalYears;

	/**
	 * Array translates stored procedure defined error IDs for messages.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $db_messages;

	/**
	 * Constructor
	 *
	 * @since    1.0
	 * @params   $bsdriver           BsDriver object
	 * @params   $bsuser             BsUser object
	 * @params   $pageTitle          Page title
	 * @params   $dbProcList         Name of stored procedure for listing page items
	 * @params   $dbProcArgs         Name of stored procedure for listing page items
	 * @params   $dbProcParms        Array of parameters to stored procedure
	 * @params   $onDeleteMsg        Message if item deleted.
	 * @returns  void
	 */
	function __construct(
		$bsdriver,
		$bsuser,
		$pageTitle,
		$arrayOfGvColumns,
		$dbProcList,
		$dbProcArgs,
		$dbProcParms,
		$onDeleteMsg
	)
	{
		parent::__construct(
			$bsdriver,
			$bsuser,
			$pageTitle,
			$arrayOfGvColumns,
			$dbProcList,
			$dbProcArgs,
			$dbProcParms,
			$onDeleteMsg
			);

		$this->FiscalYear_Count = 0;

		$this->db_messages = array(
			'FyInsertYear'    => __( 'Fiscal Year field is required' ),
			'FyInsertDup'     => __( 'Fiscal Year  already exists' ),
			'FyUpdDup'        => __( 'Title already exists' ),
			);
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

		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text( __( 'The table below lists the fiscal years that are assigned to objectives.' ) );
		$this->description_list_start();
		$this->description_list_item(
			__(
				'To sort the table, click on <b>Fiscal Year</b>, <b>People</b>,<b>Changed</b>, or <b>By</b>.'
				)
			);
		if ( $bsuser->is_admin() ) {
			$this->description_list_item(
			__(
				"To edit a fiscal year's title, click on <b>Edit</b>, change value, and click on <b>Update</b>."
				)
			);
			$this->description_list_item(
			__(
				'To delete an unreferenced fiscal year, click on <b>Delete</b>.'
				)
			);
			$this->description_list_item(
			__(
				'To add a new fiscal year, enter <b>New Info</b> values and click on <b>Add</b>'
				)
			);
			$this->description_list_item(
			__(
				'To extract a spreadsheet of these values, click on <b>Spreadsheet Download</b>'
				)
			);
		}
		$this->description_list_end();
		$this->description_end();
		$this->_Description .= $bsdriver->title_heading( __( 'Fiscal Years' ) );
	}

	/**
	 * Retrieve objective from database.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function trailer()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( !$bsuser->is_admin() ) {
			return '';
		} elseif ( $this->FiscalYear_Count == 0 ) {
			return "<span style='left:20px;position:relative;'> <b>" . __( 'Warning:' ) . ' <br />'
				. __( 'Before adding a fiscal year, be sure that the 1st month of the fiscal year is set properly.' )
					. '</b><br /><br />' 
				. __( 'Adding the first fiscal year disables setting of 1st month in the Organization administration page.' )
				. '<br />' 
				. __(
					'If you need to change the 1st month of the fiscal year, after adding new fiscal years, <br />'
					. 'you will need to either delete all objectives or re-initialize the balanced scorecard database components'
				. '</span>'
				);
		} else {
			return $bsdriver->extract_link( __( 'Spreadsheet Download' ), '' );
		}
	}

	/**
	 * Validate user input and insert row into database table.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_insert()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID = $_POST['C_ID'];

		if ( !$this->is_valid_dbinteger( 'ID', $C_ID ) ) {
		} else {
			$this->db_change(
				'P_FiscalYearInsert',
				'( %d, %d, %d )',
				array( $bsuser->OrgID, $bsuser->ID, $C_ID ),
				__( 'The fiscal year has been added.' ),
				$this->db_messages
			);
		}
	}

	/**
	 * Validate user input and update a row in database table.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_update()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID    = $_POST['C_ID'];
		$C_Title = trim( $_POST['C_Title'] );

		if ( !$this->is_valid_dbinteger( 'ID', $C_ID ) ) {
		} elseif ( !$this->is_valid_dbparm( 9, __( 'Title' ), $C_Title ) ) {
		} else {
			$this->db_change(
				'P_FiscalYearUpdate',
				'( %d, %d, %d, %s )',
				array( $bsuser->OrgID, $bsuser->ID, $C_ID, $C_Title ),
				__( 'The fiscal year has been updated.' ),
				$this->db_messages
			);
		}
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
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID = $_GET['id'];
		if ( !$bsdriver->platform_test_token( $_GET['token'], $C_ID ) ) {
			return $bsdriver->platform_access_denied();
		}
		if ( !$this->is_valid_dbinteger( 'ID', $C_ID ) ) {
		} else {
			$this->db_change(
				'P_FiscalYearDelete',
				'( %d, %d, %d )',
				array( $bsuser->OrgID, $bsuser->ID, $C_ID ),
				__( 'The fiscal year has been deleted.' ),
				$this->db_messages
			);
		}
	}

	/**
	 * Return a row of a gridview that user click edit on.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_edit_this( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID            = $row[$bsdriver->Field->C_ID];
		$C_Title         = $row[$bsdriver->Field->C_Title];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		return	ObjtrackerEasyStatic::table_td(
					'',
					$C_ID . "<input type='hidden' name='C_ID' value='" . $C_ID . "' />"
						. " <input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
					)
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title' value='" . stripslashes( $C_Title ) . "' />" )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td(
					'',
					$bsdriver->Input0SubmitUpdate
					. $bsdriver->Input0SubmitCancel
					);
	}

	/**
	 * Return a row of a gridview that user click update on but had an error.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_update_this( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID            = $row[$bsdriver->Field->C_ID];
		$C_Title         = $_POST['C_Title'];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		return	ObjtrackerEasyStatic::table_td(
					'',
					$C_ID . "<input type='hidden' name='C_ID' value='" . $C_ID . "' />"
						. "	<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
					)
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Usage ) )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title' value='" . stripslashes( $C_Title ) . "' />" )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td(
					'',
					$bsdriver->Input0SubmitUpdate
					. $bsdriver->Input0SubmitCancel
					);
	}

	/**
	 * Return a row of a gridview other than user click update or add.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_other( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID            = $row[$bsdriver->Field->C_ID];
		$C_Title         = $row[$bsdriver->Field->C_Title];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		// Make usage more readable and a bigger target for clicking
		$usageLink = ( $C_Usage == 0 )
			? __( 'None' )
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm
			. 'sc_menu=Usage&Table=T_Objective&Column=FiscalYear&Value=' . $C_ID . "'>" . __( 'Yes:' ) . $C_Usage . '</a>';

		return	ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $usageLink ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' );
	}

	/**
	 * Setup fiscal years based on the database results
	 *
	 * @since    1.0
	 * @returns  void
	 */
	public function on_db_results()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$months                   = array( 'Jan', 'Apr', 'Jul', 'Oct' );
		$firstMonth               = $months[ $bsuser->FirstMonth / 3];
		$this->MissingFiscalYears = array();
		$definedYears             = array();

		if ( count( $this->GvResults ) ) {
			$row1st   = $this->GvResults[0];
			$rowLast  = $this->GvResults[$this->RowCount - 1];
			$year1st  = $row1st[$bsdriver->Field->C_ID];
			$yearLast = $rowLast[$bsdriver->Field->C_ID];
			if ( $yearLast < 2010 )
				$yearLast = date( 'Y' );

			foreach ( $this->GvResults as $row ) {
				if ( $row[$bsdriver->Field->C_ID] > 2010 ) {
					array_push( $definedYears, $row[$bsdriver->Field->C_ID] );
		//			$bsdriver->trace_text( 'define(' . $row[$bsdriver->Field->C_ID] . ')' );
				}
			}
		} else {
			$year1st  = $bsuser->FiscalYear1 - 1;
			$yearLast = $bsuser->FiscalYear1 + 1;
		}
		for ( $i = $year1st - 1; $i < $yearLast + 2; $i++ ) {
			$bsdriver->trace_text( 'M?(' . $i . ')' );
			if ( $i > 2010 && array_search( $i, $definedYears ) === false ) {
				$txt = $bsuser->FirstMonth == 1
					? sprintf( '%s %2d', $firstMonth, $i )
					: sprintf( '%s %2d - %2d', $firstMonth, $i, $i + 1 );
				array_push( $this->MissingFiscalYears, array( $i, $txt ) );
	//			$bsdriver->trace_text( 'Missing(' . $i . ')' );
			}
		}

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

		$C_ID                 = $row[$bsdriver->Field->C_ID];
		$C_FormatedFiscalYear = $row[$bsdriver->Field->C_FormatedFiscalYear];
		$C_Title              = $row[$bsdriver->Field->C_Title];
		$C_Usage              = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed      = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid       = $row[$bsdriver->Field->C_Track_Userid];

		$this->FiscalYear_Count++;

		// Only admins can edit
		$editButton = ( !$bsuser->is_admin() )
			? ''
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=edit&id=' . $C_ID . "'>" . __( 'Edit' ) . '</a>';

		// Only admins can delete when row isn't in use
		$token        = $bsdriver->platform_get_token( $C_ID );
		$deleteButton = ( !$bsuser->is_admin() || $C_Usage > 0 )
			? ''
			: "&nbsp;<a onclick='javascript:return confirm(&#39;" . $this->OnDeleteMsg . "&#39;);' id='DeleteLinkButton_1' href='"
				. $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=delete&token=' . $token . '&id=' . $C_ID . "'>" . 'Delete' . '</a>';

		// Make usage more readable and a bigger target for clicking
		$usageLink = ( $C_Usage == 0 )
			? __( 'None' )
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm
				. 'sc_menu=Usage&Table=T_Objective&Column=FiscalYear&Value=' . $C_ID . "'>" . __( 'Yes:' ) . $C_Usage . '</a>';

		return	ObjtrackerEasyStatic::table_td( '', stripslashes( $C_FormatedFiscalYear ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $usageLink ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td( '',  $editButton . $deleteButton );
	}

	/**
	 * Return a footer row of a gridview when there was an error in prior add.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_footer_error()
	{
		$bsdriver = $this->bsdriver;
		$fyList   = $this->fy_select_options( $_POST['C_ID'] );

		return ObjtrackerEasyStatic::table_tr_start( "class='BssGvAddRow'" )
				. ObjtrackerEasyStatic::table_td(
					'',
					"&nbsp;<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
						. " <select name='C_ID' id='C_ID'>" . $fyList . '</select>'
					)
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', $bsdriver->Input0SubmitAdd )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return a footer row of a gridview.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_footer()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		// phpcs doesn't like if below if these two lines are removed 
		// -- ERROR | Opening brace should be on the same line as the declaration
		if ( $bsuser->is_admin() ) {
			$fyList = $this->fy_select_options( -1 );

			return ObjtrackerEasyStatic::table_tr_start( "class='BssGvAddRow'" )
				. ObjtrackerEasyStatic::table_td(
					'',
					"&nbsp;<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "'/>"
						. " <select name='C_ID' id='C_ID'>" . $fyList . '</select>'
					)
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', $bsdriver->Input0SubmitAdd )
				. ObjtrackerEasyStatic::table_tr_end();
		} else {
			return '';
		}
	}

	/**
	 * Fill in the options for a dropdownbox of fiscal years
	 *
	 * @since    1.0
	 * @returns  string        Select's option values.
	 */
	function fy_select_options( $selected )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		// Pick best when not specific, first >= current fiscal year
		if ( $selected == -1 ) {
			foreach ( $this->MissingFiscalYears as $pair ) {
				if ( $pair[0] >= $bsuser->FiscalYear1 ) {
					$selected = $pair[0];
					break;
				}
			}
		}

		$txt = '';
		foreach ( $this->MissingFiscalYears as $pair ) {
			$i = 0;
			if ( $selected == -1 && $i = 0 )
				$txt .= "<option value='" . $pair[0] . "' selected='selected'>" . $pair[1] . '</option>';
			elseif ( $selected == $pair[0])
				$txt .= "<option value='" . $pair[0] . "' selected='selected'>" . $pair[1] . '</option>';
			else
				$txt .= "<option value='" . $pair[0] . "'>" . $pair[1] . "</option>\n";
			$i++;
		}
		return $txt;
	}
}

?>
