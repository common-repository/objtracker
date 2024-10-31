<?php
/**
 * Superadmin's page for managing multiple organizations.
 *
 *	 Requirements:
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
function bs_orgs( $bsdriver, $bsuser )
{
	$configPage = new BsOrgsPage(
		$bsdriver,
		$bsuser,
		__( 'Organizations' ),	// Parm: Title
		array(					// Parm: Gridview columns
			new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
			new ObjtrackerGvColumn( __( 'Usage' ), 'C_Usage' ),
			new ObjtrackerGvColumn( __( 'Title' ), 'C_Title' ),
			new ObjtrackerGvColumn( __( 'Upload Path' ), 'C_UploadFsPath' ),
			new ObjtrackerGvColumn( __( 'Changed' ), 'C_Track_Changed' ),
			new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
			new ObjtrackerGvColumn( '', '' ),
			),
		'P_OrgList',
		'( %d, %d )',
		array( $bsuser->OrgID, $bsuser->ID ),
		__( 'Are you sure you want to delete this sites?' )
		);
	return $configPage->Response();
}

/**
 * Superadmin organization processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsOrgsPage extends ObjtrackerConfigPage
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
			'OrgInsTitle'	=> __( 'Title field is required' ),
			'OrgInsPath'	=> __( 'Upload path is required' ),
			'OrgInsPath'	=> __( 'Title already exists' ),
			'OrgUpdTitle'	=> __( 'Title is required' ),
			'OrgUpdPath'	=> __( 'Upload path is required' ),
			'OrgUpdTitle'	=> __( 'Title is required' ),
			'OrgUpdTitle2'	=> __( 'Short Title is required' ),
			'OrgUpdateDup'	=> __( 'Upload path is required' ),
			'OrgUpdTitle'	=> __( 'Title is required' ),
			'OrgUpdTitle2'	=> __( 'Short Title is required' ),
			'OrgUpdPath'	=> __( 'Upload path is required' ),
			);
	}

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
			$bsdriver->trace_text( 'get hidden Sortfield ' . $this->HiddenSortDirection . ' ' . $this->HiddenSortField );
		} else {
			$this->HiddenSortField     = '' ;
			$this->HiddenSortDirection = '';
		}
		$bsdriver->trace_text( 'hidden(' . $this->HiddenSortDirection . $this->HiddenSortField . ')' );

		$message          = '';
		$this->ActionChar = substr( $bsdriver->Action, 0, 1 );
		$bsdriver->trace_text( ' action(' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		switch ( $this->ActionChar ) {
			case self::BSPAGE_ADD: // User press "Add" button
				$this->row_insert();
				if ( $this->UserError )
					return $this->row_new();
				$this->ActionChar = self::BSPAGE_LIST;
				break;
			case self::BSPAGE_EDIT: // User press "Edit" button
				break;
			case self::BSPAGE_UPD: // User press "Update" button
				break;
			case self::BSPAGE_DEL: // User press "Delete" button
				$this->row_delete();
				break;
			case self::BSPAGE_NEW: // User press "New" button
				return $this->row_new();
				break;
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
			case self::BSPAGE_RESET: 	// User press "other" button
				$this->row_reset();		// People's passwords
				break;
			case self::BSPAGE_CHA: 		// User press "other" button
				$this->other_button();	// Default password
				break;
			default:					// Initial or no action
				break;
		}
		$prefix = $this->preface();
		$this->description();
		$prefix2 = $this->preface2();

		$this->GvResults = $bsdriver->platform_db_query( $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );
		$this->RowCount  = count( $this->GvResults );
		return
			"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
			. $bsdriver->platform_start_form( '', '' )
			. " <input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />\n"
			. $prefix
			. $this->_Description
			. $prefix2
			. "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . "sc_menu=Orgs&sc_action=new'>New</a>"
			. $this->ValidationMsg
			. $this->gridview1()
			. $this->trailer()
			. $bsdriver->EndForm;
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

		$this->UserInput                 = new ObjtrackerFormField();
		$this->UserInput->C_Title        = trim( $_POST['C_Title'] );
		$this->UserInput->C_UploadFsPath = trim( $_POST['C_UploadFsPath'] );

		$bsdriver->trace_text( 'row insert' );
		if ( !$this->is_valid_dbparm( 64, __( 'Title' ), $this->UserInput->C_Title ) ) {
		} elseif ( !$this->is_valid_dbparm( 150, __( 'Upload Path' ), $this->UserInput->C_UploadFsPath ) ) {
		} else {
			$bsdriver->trace_text( ' <br>C_Title=' . $this->UserInput->C_Title );
			$bsdriver->trace_text( ' <br>C_UploadFsPath=' . $this->UserInput->C_UploadFsPath );
			$this->db_change(
				'P_OrgInsert',
				'( %d, %d, %s, %s )',
				array(
					$bsuser->OrgID, $bsuser->ID,
					$this->UserInput->C_Title, $this->UserInput->C_UploadFsPath,
					),
				__( 'The site has been added.' ),
				$this->db_messages
			);
		}
	}

	/**
	 * Build form input lines for a new item
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_new()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->setpage_description_head( __( 'Adding new site' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text( __( 'Fill in fields below and select first site administrator.' ) );
		$this->description_end();

		return
			"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
			. $bsdriver->platform_start_form( '', '' )
			. " <input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />\n"
			. $this->_Description
			. $txt = $bsdriver->description_headertext( __( 'New Organization' ) )
			. $this->ValidationMsg
			. ObjtrackerEasyStatic::table_start( "cellspacing='0' id='BsFV1' class='BssFormview' style='width:700px;border-collapse:collapse;'>" )
			. $this->row_title()
			. $this->row_uploadpath()
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td( "style='width:25%", '&nbsp;' )
			. ObjtrackerEasyStatic::table_td( "style='text-align: center'", $bsdriver->Input0SubmitAdd . $bsdriver->Input0SubmitCancel )
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_end()
			. $bsdriver->EndForm;
	}

	/**
	 * Build form input line for display/edit of a title.
	 *
	 * @since    1.0
	 * @returns  string        Segment of html for display/edit of a title.
	 */
	function row_title()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$style = " maxlength='64'";
		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$cell = "<input type='text' name='C_Title' value='' " . $style . ' />';
		} else {
			$cell = "<input type='text' name='C_Title' value='" . $this->UserInput->C_Title . "' " . $style . ' />';
		}

		return ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%", '<b>' . __( 'Title' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $cell )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Build form input line for display/edit of a upload path.
	 *
	 * @since    1.0
	 * @returns  string        Segment of html for display/edit of a upload path.
	 */
	function row_uploadpath()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$style = " maxlength='150'";
		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$cell = "<input type='text' name='C_UploadFsPath' value='' " . $style . ' />';
		} else {
			$cell = "<input type='text' name='C_UploadFsPath' value='" . $this->UserInput->C_UploadFsPath . "' " . $style . ' />';
		}

		return ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%", '<b>' . __( 'Upload path' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $cell )
				. ObjtrackerEasyStatic::table_tr_end();
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
		$this->description_text( __( 'The table below lists the organizations.' ) );

		$this->description_list_start();
		$this->description_list_item(
			__(
				'To sort the table, click on <b>ID</b>, <b>Usage</b>, <b>Title</b>, <b>Upload Path</b>,
				 <b>Changed</b>, or <b>By</b>.'
				)
			);
		$this->description_list_item(
			__(
				'To edit an organization, click on <b>Edit</b>, change values, and click on <b>Update</b>.'
				)
			);
		$this->description_list_item( __( 'To delete an organization, click on <b>Delete</b>.' ) );
		$this->description_list_item( __( 'To add a new organization, enter <b>New Info</b> values and click on <b>Add</b>' ) );
		$this->description_list_item( __( 'To extract a spreadsheet of these values, click on <b>Spreadsheet Download</b>' ) );
		$this->description_list_end();

		$this->description_end();
		$this->_Description .= $bsdriver->title_heading( __( 'Organizations' ) );
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

		return $bsdriver->extract_link( __( 'Spreadsheet Download' ), '' );
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

		$C_ID           = $_POST['C_ID'];
		$C_Title        = trim( $_POST['C_Title'] );
		$C_UploadFsPath = trim( $_POST['C_UploadFsPath'] );

		if ( !$this->is_valid_dbinteger( 'ID', $C_ID ) ) {
		} elseif ( !$this->is_valid_dbparm( 64, __( 'Title' ), $C_Title ) ) {
		} elseif ( !$this->is_valid_dbparm( 150, __( 'Upload path' ), $C_UploadFsPath ) ) {
		} else {
			$this->db_change(
				'P_OrgUpdate',
				'( %d, %s, %s )',
				array( $C_ID, $C_Title, $C_UploadFsPath ),
				__( 'The site has been updated.' ),
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
				'P_OrgDelete',
				'( %d )',
				array( $C_ID ),
				__( 'The site has been deleted.' ),
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
		$C_UploadFsPath  = $row[$bsdriver->Field->C_UploadFsPath];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		return	ObjtrackerEasyStatic::table_td(
					'',
					$C_ID . "<input type='hidden' name='C_ID' value='" . $C_ID . "' />"
					. " <input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
					)
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Usage ) )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title' value='" . $C_Title . "' />" )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_UploadFsPath' value='" . $C_UploadFsPath . "' />" )
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
		$C_UploadFsPath  = $_POST['C_UploadFsPath'];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		return	ObjtrackerEasyStatic::table_td(
					'',
					$C_ID . "<input type='hidden' name='C_ID' value='" . $C_ID . "' />"
						. "	<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
					)
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Usage ) )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title' value='" . $C_Title . "' />" )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_UploadFsPath' value='" . $C_UploadFsPath . "' />" )
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
		$C_UploadFsPath  = $row[$bsdriver->Field->C_UploadFsPath];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		return	ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Usage ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_UploadFsPath ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' );
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
		$C_ID2           = $C_ID == 0 ? 'Model' : $C_ID;
		$C_Title         = $row[$bsdriver->Field->C_Title];
		$C_UploadFsPath  = $row[$bsdriver->Field->C_UploadFsPath];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		// Only admins can edit
		$editButton = "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=edit&id=' . $C_ID . "'>" . __( 'Edit' ) . '</a>';

		$token        = $bsdriver->platform_get_token( $C_ID );
		$deleteButton = $C_ID == 0
			? ''
			: "&nbsp;<a onclick='javascript:return confirm(&#39;" . $this->OnDeleteMsg . "&#39;);' id='DeleteLinkButton_1' href='"
				. $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=delete&token=' . $token . '&id=' . $C_ID . "'>" . 'Delete' . '</a>';

		return	ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID2 ) )
				. ObjtrackerEasyStatic::table_td( '', $C_Usage )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_UploadFsPath ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td( '', $editButton . $deleteButton );
	}
}

?>
