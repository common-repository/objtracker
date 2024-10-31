<?php
/**
 * Administrator's page for managing departments.
 *
 *	Requirements:
 *		o Only admins can add, update, or delete
 *		o Names of departments must be unique, stored proc checks
 *		o Must not delete department with people
 *		o Link to people of department
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
function bs_departments( $bsdriver, $bsuser )
{

	$configPage = new BsDepartmentsPage(
		$bsdriver,
		$bsuser,
		__( 'Departments' ),		// Parm: Title
		array(				// Parm: Gridview columns
			new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
			new ObjtrackerGvColumn( __( 'People' ), 'C_Usage' ),
			new ObjtrackerGvColumn( __( 'Title' ), 'C_Title' ),
			new ObjtrackerGvColumn( __( 'Short Title' ), 'C_Title2' ),
			new ObjtrackerGvColumn( __( 'Active' ), 'C_IsActive' ),
			new ObjtrackerGvColumn( __( 'Changed' ), 'C_Track_Changed' ),
			new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
			new ObjtrackerGvColumn( '', '' ),
			),
		'P_DepartmentList',
		'( %d, %d, %s, %s, %s )',
		array( $bsuser->OrgID, $bsuser->ID, 'False', 'False', 'Unused' ),
		__( 'Are you sure you want to delete this department?' )
		);
	return $configPage->Response();
}

/**
 * Department processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsDepartmentsPage extends ObjtrackerConfigPage
{
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

		$this->db_messages = array(
			'DeptDel1' 			=> __( 'Cannot delete last department' ),
			'DeptInsTitle'		=> __( 'Title f	ield is required' ),
			'DeptInsTitle2'		=> __( 'Short title field is required' ),
			'DeptInsDupTitle'	=> __( 'Title already exists' ),
			'DeptInsDupTitle2'	=> __( 'Short title already exists' ),
			'DeptUpdTitle'		=> __( 'Title not specified' ),
			'DeptUpdTitle2'		=> __( 'Short title not specified' ),
			'DeptUpdDupTitle'	=> __( 'Title already exists' ),
			'DeptUpdDupTitle2'	=> __( 'Short title already exists' ),
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
		$this->description_text( __( 'The table below lists the departments of people who are assigned to objectives.' ) );
		$this->description_list_start();
		$this->description_list_item(
			__(
				'To sort the table, click on <b>ID</b>, <b>People</b>, <b>Title</b>, <b>Short Title</b>,
					<b>Active</b>, <b>Changed</b>, or <b>By</b>.'
				)
			);
		if ( $bsuser->is_admin() ) {
			$this->description_list_item(
			__(
				'To edit a department, click on <b>Edit</b>, change values, and click on <b>Update</b>.'
				)
			);
			$this->description_list_item( __( 'To delete an unreferenced department, click on <b>Delete</b>.' ) );
			$this->description_list_item(
			__(
				'To add a new department, enter <b>New Info</b> values and click on <b>Add</b>'
				)
			);
			$this->description_list_item( __( 'To extract a spreadsheet of these values, click on <b>Spreadsheet Download</b>' ) );
		}
		$this->description_list_end();
		$this->description_end();
		$this->_Description .= $bsdriver->title_heading( __( 'Departments' ) );
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

		if ( $bsuser->is_admin() )
			return $bsdriver->extract_link(
					'Spreadsheet Download',
					'&A=C_Active&V=False&A2=C_All&V2=False&A3=C_Userid&V3=False'
					);
		else
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
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_Title  = trim( $_POST['C_Title'] );
		$C_Title2 = trim( $_POST['C_Title2'] );

		if ( !$this->is_valid_dbparm( 64, __( 'Title' ), $C_Title ) ) {
		} elseif ( !$this->is_valid_dbparm( 16, __( 'Short title' ), $C_Title2 ) ) {
		} else {
			$this->db_change(
				'P_DepartmentInsert',
				'( %d, %d, %s, %s )',
				array( $bsuser->OrgID, $bsuser->ID, $C_Title, $C_Title2 ),
				__( 'The department has been added.' ),
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

		$C_ID       = $_POST['C_ID'];
		$C_Title    = trim( $_POST['C_Title'] );
		$C_Title2   = trim( $_POST['C_Title2'] );
		$C_IsActive = $_POST['C_IsActive'];

		if ( !$this->is_valid_dbinteger( 'ID', $C_ID ) ) {
		} elseif ( !$this->is_valid_dbparm( 64, __( 'Title' ), $C_Title ) ) {
		} elseif ( !$this->is_valid_dbparm( 16, __( 'Short title' ), $C_Title2 ) ) {
		} elseif ( $C_IsActive != 'Yes' && $C_IsActive != 'No' ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Active value is incorrect.' ) );
		} else {
			$this->db_change(
				'P_DepartmentUpdate',
				'( %d, %d, %d, %s, %s, %s )',
				array( $bsuser->OrgID, $bsuser->ID, $C_ID, $C_IsActive, $C_Title, $C_Title2 ),
				__( 'The department has been updated.' ),
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
				'P_DepartmentDelete',
				'( %d, %d, %d )',
				array( $bsuser->OrgID, $bsuser->ID, $C_ID ),
				__( 'The department has been deleted.' ),
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
		$C_Title2        = $row[$bsdriver->Field->C_Title2];
		$C_IsActive      = $row[$bsdriver->Field->C_IsActive];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		$yesNoOptions = ( $C_IsActive == 'Yes' )
			? "<option value='Yes' selected='selected'>" . __( 'Yes' ) . "</option><option value='No' >" . __( 'No' ) . '</option>'
			: "<option value='Yes'>" . __( 'Yes' ) . "</option><option value='No' selected='selected' >" . __( 'No' ) . '</option >';

		return
			ObjtrackerEasyStatic::table_td(
				'',
				$C_ID . "<input type='hidden' name='C_ID' value='" . $C_ID . "' />"
					. " <input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
				)
			. ObjtrackerEasyStatic::table_td( '', $C_IsActive )
			. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title' value='" . stripslashes( $C_Title ) . "' />" )
			. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title2' value='" . stripslashes( $C_Title2 ) . "' />" )
			. ObjtrackerEasyStatic::table_td( '', "<select name='C_IsActive' id='C_IsActive'>" . $yesNoOptions . '</select>' )
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
		$C_Title         = trim( $_POST['C_Title'] );
		$C_Title2        = trim( $_POST['C_Title2'] );
		$C_IsActive      = $row[$bsdriver->Field->C_IsActive];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		$yesNoOptions = ( $C_IsActive == 'Yes' )
			? "<option value='Yes' selected='selected'>" . __( 'Yes' ) . "</option><option value='No' >" . __( 'No' ) . '</option>'
			: "<option value='Yes'>" . __( 'Yes' ) . "</option><option value='No' selected='selected' >" . __( 'No' ) . '</option>';

		return
			ObjtrackerEasyStatic::table_td(
				'',
				$C_ID . "<input type='hidden' name='C_ID' value='" . $C_ID . "' />"
				. " <input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
				)
			. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Usage ) )
			. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title' value='" . stripslashes( $C_Title ) . "' />" )
			. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title2' value='" . stripslashes( $C_Title2 ) . "' />" )
			. ObjtrackerEasyStatic::table_td( '', "<select name='C_IsActive' id='C_IsActive'>" . $yesNoOptions . '</select>' )
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
		$C_Title2        = $row[$bsdriver->Field->C_Title2];
		$C_IsActive      = $row[$bsdriver->Field->C_IsActive];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		return
			ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
			. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Usage ) )
			. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title ) )
			. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title2 ) )
			. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_IsActive ) )
			. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
			. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
			. ObjtrackerEasyStatic::table_td( '', '&nbsp;' );
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
		$C_Title         = $row[$bsdriver->Field->C_Title];
		$C_Title2        = $row[$bsdriver->Field->C_Title2];
		$C_IsActive      = $row[$bsdriver->Field->C_IsActive];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		// Only admins can edit
		$editButton = ( !$bsuser->is_admin() )
			? ''
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=edit&id=' . $C_ID . "'>" . __( 'Edit' ) . '</a>';

		// Only admins can delete when row isn't only or in use
		$token      = $bsdriver->platform_get_token( $C_ID );
		$deleteButton = ( !$bsuser->is_admin() || $this->RowCount == 1 || $C_Usage > 0 )
			? ''
			: "&nbsp;<a onclick='javascript:return confirm(&#39;" . $this->OnDeleteMsg . "&#39;);' id='DeleteLinkButton_1' href='"
				. $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=delete&token=' . $token . '&id=' . $C_ID . "'>" . 'Delete' . '</a>';

		// Make usage more readable and a bigger target for clicking
		$usageLink = ( $C_Usage == 0 )
			? __( 'None' )
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Usage&Table=T_Person&Column=DepartmentID&Value='
				. $C_ID . "'>" . __( 'Yes:' ) . $C_Usage . '</a>';

		return	ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '', $usageLink )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title2 ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_IsActive ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td( '', $editButton . $deleteButton );
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

		return ObjtrackerEasyStatic::table_tr_start( "class='BssGvAddRow'" )
			. ObjtrackerEasyStatic::table_td( '', "&nbsp;<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />" )
			. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
			. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title' value='" . stripslashes( $_POST['C_Title'] ) . "' />" )
			. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title2' value='" . stripslashes( $_POST['C_Title2'] ) . "' />" )
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

		if ( $bsuser->is_admin() )
			return ObjtrackerEasyStatic::table_tr_start( "class='BssGvAddRow'" )
				. ObjtrackerEasyStatic::table_td( '', "&nbsp;<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />" )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title' value='' style='width:250px' />" )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title2' value='' /></td>" )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', $bsdriver->Input0SubmitAdd )
				. ObjtrackerEasyStatic::table_tr_end();
		else
			return '';
	}
}

?>
