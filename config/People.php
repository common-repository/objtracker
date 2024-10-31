<?php
/**
 * Administator's page for managing users of the scorecard.
 *
 *	 Requirements:
 *		o Only admins can add,update, or delete
 *		o Names of departments must be unique, stored procedure validates
 *		o Must not delete
 *			o Person with objectives
 *			o Self
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
function bs_people( $bsdriver, $bsuser )
{
	$configPage = new BsPeoplePage(
		$bsdriver,
		$bsuser,
		__( 'People' ),		// Parm: PageTitle
		array(				// Parm: Gridview columns
			new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
			new ObjtrackerGvColumn( __( 'Objectives' ), 'C_Usage' ),
			new ObjtrackerGvColumn( __( 'Logon ID' ), 'C_UserName' ),
			new ObjtrackerGvColumn( __( 'Full Name' ), 'C_FullName' ),
			new ObjtrackerGvColumn( __( 'Admin' ), 'C_IsAdmin' ),
			new ObjtrackerGvColumn( __( 'Active' ), 'C_IsActive' ),
			new ObjtrackerGvColumn( __( 'Department' ), 'C_Department' ),
			new ObjtrackerGvColumn( __( 'Changed' ), 'C_Track_Changed' ),
			new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
			new ObjtrackerGvColumn( '', '' ),
			),
		'P_PersonList',
		'( %d, %d, %s )',
		array( $bsuser->OrgID, $bsuser->ID, 'True' ),
		__( 'Are you sure you want to delete this person?' )
		);

	$configPage->get_db_departments( 'false' );
	$configPage->LastNewPasswordMsg = '';

	return $configPage->Response();
}

/**
 * People processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsPeoplePage extends ObjtrackerConfigPage
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
	 * @params   $dbProcParms        Array of parameters to stored procedure
	 * @params   $dbProcArgs         Name of stored procedure for listing page items
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
			'PerInsName'	=> __( 'Full name not specified' ),
			'PerInsLogon'	=> __( 'Logon ID not specified' ),
			'PerInsDup'		=> __( 'Login ID already exists' ),
			'PerUpdName'	=> __( 'Full name not specified' ),
			'PerUpdLogon'	=> __( 'Logon ID not specified' ),
			'PerUpdDup'		=> __( 'Login ID already exists' ),
			);
	}

	/**
	 * Holds an array of department names.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $Departments;

	/**
	 * If plugin deals with passwords, ...
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	public $LastNewPasswordMsg;

	/**
	 * Retrieve departments from the database.
	 *
	 * @since    1.0
	 * @param    string $active 'True' or 'False' indicting if want active only.
	 * @returns  void
	 */
	function get_db_departments( $active )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->Departments = array();

		$results = $bsdriver->platform_db_query(
			'P_DepartmentList',
			'( %d, %d, %s, %s, %s )',
			array(
				$bsuser->OrgID, $bsuser->ID,
				$active, 'False', 'dontcare',
				)
			);

		foreach ( $results as $row ) {
			$C_ID       = $row[$bsdriver->Field->C_ID];
			$C_Title    = $row[$bsdriver->Field->C_Title];
			$C_IsActive = $row[$bsdriver->Field->C_IsActive];
			if ( $C_IsActive == 'Yes' ) {
				array_push( $this->Departments, array( $C_ID, $C_Title ) );
			} else {
				array_push( $this->Departments, array( $C_ID, __( '(inactive) ' ) . $C_Title ) );
			}
		}
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
		$this->description_text( __( 'The table below lists the people who are assigned to objectives and and serves as the source for logon user IDs.' ) );
		$this->description_list_start();
		$this->description_list_item(
			__(
				'To sort the table, click on <b>ID</b>, <b>Objectives</b>, <b>Full Name</b>, <b>Logon
				ID</b>, <b>Department</b>, <b>Admin</b>, <b>Active</b>, <b>Changed</b>, or <b>By</b>'
				)
			);
		if ( $bsuser->is_admin() ) {
			$this->description_list_item(
			__(
				'To edit a person, click on <b>Edit</b>, change values, and click on <b>Update</b>'
				)
			);
			$this->description_list_item( __( 'To delete an unreferenced person, click on <b>Delete</b>' ) );
			$this->description_list_item( __( 'To add a new person, enter <b>New Info</b> values and click on <b>Add</b>' ) );
			$this->description_list_item( __( 'To extract a spreadsheet of these values, click on <b>Spreadsheet Download</b>' ) );
			if ( $bsdriver->ManagesPasswords ) {
				$this->description_list_item( __( "To reset the user's password, click on <b>Reset</b>" ) );
			}
		}
		$this->description_list_end();
		$this->description_end();
		$this->_Description .= $bsdriver->title_heading( __( 'People' ) );
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
			return $bsdriver->extract_link( __( 'Spreadsheet Download' ), '&A=C_OrganizationID&V=1&A2=C_Inactive&V2=True' );
		else
			return '';
	}

	/**
	 * If scorecard actually manages passwords ...
	 *
	 * @since    1.0
	 * @returns  string         Description and form for setting default passwords.
	 */
	function preface()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$txt = '';
		if ( $bsdriver->ManagesPasswords && $bsuser->is_admin() ) {
			$this->setpage_description_head( __( 'Password for next new user and/or next user reset password' ) );
			$this->description_text( __( 'To change the password, enter the Next Password, and click on Change.' ) );
			$this->description_end();
			$txt = $this->_Description;

			$txt .= 'Next password '
				. "<input type='text' name='C_NextPassword' value='' onkeyup=\"BsSetButtonStatus(this, 'submitButton2')\" />\n"
				. "<input type='submit' name='submitButton2' id='submitButton2' class='BssButton' value='Change' disabled='disabled' /><br /><br />\n";
			if ( $this->LastNewPasswordMsg != '' )
				$txt .= $this->LastNewPasswordMsg . "<br />\n";
		}
		return $txt;
	}

	/**
	 * If scorecard actually manages passwords, update default password.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function other_button()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $bsdriver->ManagesPasswords && $bsuser->is_admin() && $this->ActionChar == self::BSPAGE_CHA ) {
			$C_NextPassword = trim( $_POST['C_NextPassword'] );
			if ( $C_NextPassword != '' ) {
				$this->db_change(
					'P_DefaultPasswordUpdate',
					'( %d, %d, %s )',
					array( $bsuser->OrgID, $bsuser->ID, $_POST['C_NextPassword'] ),
					__( 'Default password changed to' ) . ' ' . $C_NextPassword,
					$this->db_messages
					);
				$this->LastNewPasswordMsg = $this->ValidationMsg;
				$this->ValidationMsg      = '';
				$this->UserError          = false;
			}
		}
	}

	/**
	 * If scorecard actually manages passwords, update default password.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_reset()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $bsdriver->ManagesPasswords && $bsuser->is_admin() ) {
			$C_ID = $_POST['C_ID'];
			$this->db_change(
				'P_DefaultPasswordReset',
				'( %d, %d, %d )',
				array( $bsuser->OrgID, $bsuser->ID, $C_ID ),
				__( 'Default password changed to' ) . ' ' . $C_NextPassword,
				$this->db_messages
				);
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

		$C_UserName     = trim( $_POST['C_UserName'] );
		$C_FullName     = trim( $_POST['C_FullName'] );
		$C_IsAdmin      = $_POST['C_IsAdmin'];
		$C_DepartmentID = $_POST['C_DepartmentID'];

		if ( !$this->is_valid_dbparm( 64, __( 'User ID' ), $C_UserName ) ) {
		} elseif ( !$this->is_valid_dbparm( 64, __( 'Full name' ), $C_FullName ) ) {
		} elseif ( $C_IsAdmin != 'Yes' && $C_IsAdmin != 'No' ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Admin specification error.' ) );
		} elseif ( !is_numeric( $C_DepartmentID ) ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Department ID (' ) . $C_DepartmentID . __( ') is not numeric.' ) );
		} else {
			$this->db_change(
				'P_PersonInsert',
				'( %d, %d, %s, %s, %s, %s )',
				array(
					$bsuser->OrgID, $bsuser->ID,
					$C_IsAdmin, $C_FullName, $C_UserName, $_POST['C_DepartmentID'],
					),
				__( 'The person has been added.' ),
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
		$C_UserName = trim( $_POST['C_UserName'] );
		$C_FullName = trim( $_POST['C_FullName'] );
		$C_IsAdmin  = $_POST['C_IsAdmin'];
		$C_IsActive = $_POST['C_IsActive'];

		if ( !$this->is_valid_dbinteger( 'ID', $C_ID ) ) {
		} elseif ( !$this->is_valid_dbparm( 64, __( 'User ID' ), $C_UserName ) ) {
			// phpcs doesn't like above 
			// -- ERROR | Opening brace should be on the same line as the declaration
		} elseif ( !$this->is_valid_dbparm( 64, __( 'Full name' ), $C_FullName ) ) {
		} elseif ( $C_IsAdmin != 'Yes' && $C_IsAdmin != 'No' ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Admin value is incorrect.' ) );
		} elseif ( $C_IsActive != 'Yes' && $C_IsActive != 'No' ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Active value is incorrect.' ) );
		} else {
			$this->db_change(
				'P_PersonUpdate',
				'( %d, %d, %d, %d, %s, %s, %s, %s )',
				array(
					$bsuser->OrgID, $bsuser->ID,
					$C_ID, $_POST['C_DepartmentID'], $C_IsAdmin, $C_IsActive, $C_FullName, $C_UserName,
					),
				__( 'The person has been updated.' ),
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
				'P_PersonDelete',
				'( %d, %d, %d )',
				array( $bsuser->OrgID, $bsuser->ID, $C_ID ),
				__( 'The person has been deleted.' ),
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
		$C_UserName      = $row[$bsdriver->Field->C_UserName];
		$C_FullName      = $row[$bsdriver->Field->C_FullName];
		$C_IsAdmin       = $row[$bsdriver->Field->C_IsAdmin];
		$C_IsActive      = $row[$bsdriver->Field->C_IsActive];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_DepartmentID  = $row[$bsdriver->Field->C_DepartmentID];
		$C_Department    = $row[$bsdriver->Field->C_Department];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		$yesNoAdmin = ( $C_IsAdmin == 'Yes' )
			? "<option value='Yes' selected='selected'>" . __( 'Yes' ) . "</option><option value='No' >" . __( 'No' ) . '</option>'
			: "<option value='Yes'>" . __( 'Yes' ) . "</option><option value='No' selected='selected' >" . __( 'No' ) . '</option>';

		$yesNoActive = ( $C_IsActive == 'Yes' )
			? "<option value='Yes' selected='selected'>" . __( 'Yes' ) . "</option><option value='No' >" . __( 'No' ) . '</option>'
			: "<option value='Yes'>" . __( 'Yes' ) . "</option><option value='No' selected='selected' >" . __( 'No' ) . '</option>';

		$departmentList = $this->departments_select_options( $C_DepartmentID );

		return 
				ObjtrackerEasyStatic::table_td(
					'',
					$C_ID . "<input type='hidden' name='C_ID' value='" . $C_ID . "' />"
					. "	<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
					)
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_UserName' value='" . $C_UserName . "' />" )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_FullName' value='" . $C_FullName . "' />" )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_IsAdmin' id='C_IsAdmin'>" . $yesNoAdmin . '</select>' )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_IsActive' id='C_IsActive'>" . $yesNoActive . '</select>' )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_DepartmentID' id='C_DepartmentID'>" . $departmentList . '</select>' )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td(
					'',
					$bsdriver->Input0SubmitUpdate . '<br />'
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
		$C_UserName      = $row[$bsdriver->Field->C_UserName];
		$C_FullName      = $row[$bsdriver->Field->C_FullName];
		$C_IsAdmin       = $row[$bsdriver->Field->C_IsAdmin];
		$C_IsActive      = $row[$bsdriver->Field->C_IsActive];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_DepartmentID  = $row[$bsdriver->Field->C_DepartmentID];
		$C_Department    = $row[$bsdriver->Field->C_Department];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		$yesNoAdmin = ( $C_IsAdmin == 'Yes' )
			? "<option value='Yes' selected='selected'>" . __( 'Yes' ) . "</option><option value='No' >" . __( 'No' ) . '</option>'
			: "<option value='Yes'>" . __( 'Yes' ) . "</option><option value='No' selected='selected' >" . __( 'No' ) . '</option>';

		$yesNoActive = ( $C_IsActive == 'Yes' )
			? "<option value='Yes' selected='selected'>" . __( 'Yes' ) . "</option><option value='No' >" . __( 'No' ) . '</option>'
			: "<option value='Yes'>" . __( 'Yes' ) . "</option><option value='No' selected='selected' >" . __( 'No' ) . '</option>';

		$departmentList = $this->departments_select_options( $_POST['C_DepartmentID'] );

		return
				ObjtrackerEasyStatic::table_td(
					'',
					$C_ID . "<input type='hidden' name='C_ID' value='" . $C_ID . "' />"
					. "	<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
					)
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_UserName' value='" . $C_UserName . "' />" )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_FullName' value='" . $C_FullName . "' />" )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_IsAdmin' id='C_IsAdmin'>" . $yesNoAdmin . '</select>' )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_IsActive' id='C_IsActive'>" . $yesNoActive . '</select>' )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_DepartmentID' id='C_DepartmentID'>" . $departmentList . '</select>' )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td(
					'',
					$bsdriver->Input0SubmitUpdate . '<br />'
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
		$C_UserName      = $row[$bsdriver->Field->C_UserName];
		$C_FullName      = $row[$bsdriver->Field->C_FullName];
		$C_IsAdmin      = $row[$bsdriver->Field->C_IsAdmin];
		$C_IsActive      = $row[$bsdriver->Field->C_IsActive];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_DepartmentID  = $row[$bsdriver->Field->C_DepartmentID];
		$C_Department    = $row[$bsdriver->Field->C_Department];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		return
				ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_UserName ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_FullName ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_IsAdmin ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_IsActive ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Department ) )
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
		$C_UserName      = $row[$bsdriver->Field->C_UserName];
		$C_FullName      = $row[$bsdriver->Field->C_FullName];
		$C_IsAdmin       = $row[$bsdriver->Field->C_IsAdmin];
		$C_IsActive      = $row[$bsdriver->Field->C_IsActive];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_DepartmentID  = $row[$bsdriver->Field->C_DepartmentID];
		$C_Department    = $row[$bsdriver->Field->C_Department];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		// Only admins can edit
		$editButton = ( !$bsuser->is_admin() ) 
			? '' 
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=edit&id=' . $C_ID . "'>" . __( 'Edit' ) . '</a>';

		// Only admins can delete when row isn't only or in use or is self
		if ( $bsuser->is_admin() ) {
			if ( $this->RowCount == 1 ) {
				$deleteButton = '';
			} elseif ( $bsuser->ID == $C_ID ) {
				$deleteButton = '';
			} elseif ( $C_Usage > 0 ) {
				$deleteButton = '';
			} else {
				$token        = $bsdriver->platform_get_token( $C_ID );
				$deleteButton = "&nbsp;<a onclick='javascript:return confirm(&#39;" . $this->OnDeleteMsg . "&#39;);' id='DeleteLinkButton_1' href='" 	
					. $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
					. $bsdriver->MenuName . '&sc_action=delete&token=' . $token . '&id=' . $C_ID . "'>" . 'Delete' . '</a>';
			}

			if ( $bsdriver->ManagesPasswords )
				$resetButton = "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=reset&id=' . $C_ID . "'>" . 'Reset' . '</a>'; 
			else
				$resetButton = '';
		} else {
			$deleteButton = '';
			$resetButton  = '';
		}
	
		$usageLink = ($C_Usage == 0) 
			? __( 'None' )
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm
				. 'sc_menu=Usage&Table=T_Objective&Column=OwnerID&Value=' . $C_ID . "'>" . __( 'Yes:' ) . $C_Usage . '</a>';
	
		return	 ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $usageLink ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_UserName ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_FullName ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_IsAdmin ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_IsActive ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Department ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td( '', $editButton . $resetButton . $deleteButton );
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

		$this->get_db_departments( 'true' );
		$yesNoAdmin = ( $_POST['C_IsAdmin'] == 'Yes' )
			? "<option value='Yes' selected='selected'>" . __( 'Yes' ) . "</option><option value='No' >" . __( 'No' ) . '</option>'
			: "<option value='Yes'>" . __( 'Yes' ) . "</option><option value='No' selected='selected' >" . __( 'No' ) . '</option>';

		$departmentList = $this->departments_select_options( $_POST['C_DepartmentID'] );

		return ObjtrackerEasyStatic::table_tr_start( "class='BssGvAddRow'" )
				. ObjtrackerEasyStatic::table_td( '', "&nbsp;<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />" )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_UserName' value='" . $_POST['C_UserName'] . "' />" )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_FullName' value='" . $_POST['C_FullName'] . "' />" )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_IsAdmin' id='C_IsAdmin'>" . $yesNoAdmin . '</select>' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_DepartmentID' id='C_DepartmentID'>" . $departmentList . '</select>' )
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

		if ( $bsuser->is_admin() ) {	
			$yesNoAdmin  = "<option value='Yes'>" . __( 'Yes' ) . "</option><option value='No' selected='selected' >" . __( 'No' ) . '</option>'; 
			$yesNoActive = "<option value='Yes' selected='selected'>" . __( 'Yes' ) . "</option><option value='No' >" . __( 'No' ) . '</option>';
			$this->get_db_departments( 'true' );
			$departmentList = $this->departments_select_options( -1 );

			return ObjtrackerEasyStatic::table_tr_start( "class='BssGvAddRow'" )
				. ObjtrackerEasyStatic::table_td( '', "&nbsp;<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />" )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_UserName' value='' />" )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_FullName' value='' />" )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_IsAdmin' id='C_IsAdmin'>" . $yesNoAdmin . '</select>' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<select name='C_DepartmentID' id='C_DepartmentID'>" . $departmentList . '</select>' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', $bsdriver->Input0SubmitAdd )
				. ObjtrackerEasyStatic::table_tr_end();
		} else
			return '';
	}

	/**
	 * Return html fragment of options for a select.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing html fragment of options for a select.
	 */
	function departments_select_options( $selected )
	{
		$txt = '';
		foreach ( $this->Departments as $pair ) {
			$i = 0;
			if ( $selected == -1 && $i = 0 ) 
				$txt .= "<option value='" . $pair[0] . "' selected='selected'>" . $pair[1] . '</option>';
			elseif ( $selected == $pair[0] ) 
				$txt .= "<option value='" . $pair[0] . "' selected='selected'>" . $pair[1] . '</option>';
			else
				$txt .= "<option value='" . $pair[0] . "'>" . $pair[1] . "</option>\n";
			$i++;
		}
		return $txt;
	}

}

?>
