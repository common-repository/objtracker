<?php
/**
 * Page manages files uploaded and viewed for a measurement of an objective.
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
 * @param    object  $bsdriver        The environment object
 * @param    object  $bsuser          The user object
 * @param    int     $objectiveid     ID of objective
 * @param    string  $periodstarting  Date that starts the measurement period
 * @return   string                   Page's unique html text.
 */
function bs_attachments( $bsdriver, $bsuser, $objectiveid = NULL, $periodstarting = NULL )
{
	if ( !is_null( $objectiveid ) ) {
		$ID         = $objectiveid;
		$PS         = $periodstarting;
		$fromalerts = true;
	} elseif ( isset( $_GET['ID'] ) ) {
		$ID         = $_GET['ID'];
		$PS         = $_GET['P'];
		$fromalerts = false;
	} else {
		$ID         = $_POST['ID'];
		$PS         = $_POST['P'];
		$fromalerts = false;
	}

	$configPage = new BsAttachmentsPage(
		$bsdriver,
		$bsuser,
		__( 'Attached Documents' ),	// Parm: Title
		array(					// Parm: Gridview columns:n
			new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
			new ObjtrackerGvColumn( __( 'Period Starting' ), 'C_PeriodStarting' ),
			new ObjtrackerGvColumn( __( 'Document Name' ), 'C_FileName' ),
			new ObjtrackerGvColumn( __( 'Description' ), 'C_Description' ),
			new ObjtrackerGvColumn( __( 'Added' ), 'C_Track_Added' ),
			new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
			new ObjtrackerGvColumn( '', '' ),
			),
		'P_DocumentationList',
		'( %d, %d, %d, %s )',
		array( $bsuser->OrgID, $bsuser->ID, $ID, $PS ),
		'Are you sure you want to delete this attachment?'
		);
	return $configPage->Response1( $fromalerts );
}
/**
 * Attachments processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsAttachmentsPage extends ObjtrackerConfigPage
{
	const PARM_OBJECTIVEID    = 2;
	const PARM_PERIODSTARTING = 3;
	
	/**
	 * Holds all of the user input in case it needs to be redisplayed.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $UserInput;
	/**
	 * Holds the count of entries so that go-back button returns to caller.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $Gobacks;

	/**
	 * Returns this page unique html text.
	 *
	 * @since    1.0
	 * @return   string              Page's unique html text.
	 */
	public function response1( $fromalerts )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->FromAlerts = $fromalerts;

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

		$this->UserInput = new ObjtrackerFormField();
		if ( $this->FromAlerts ) {
			$this->UserInput->C_Description = '';
			$this->Gobacks                  = 1;
		} elseif ( $bsdriver->IsPost ) {
			$this->UserInput->C_Description = trim( $_POST['C_Description'] );
			$this->Gobacks                  = $_POST['Gobacks'] + 1;
		} else {
			$this->UserInput->C_Description = '';
			$this->Gobacks                  = 1;
		}

		$message = '';
		$this->ActionChar = substr( $bsdriver->Action, 0, 1 );
		$bsdriver->trace_text( ' action(' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		switch ( $this->ActionChar ) {
			case self::BSPAGE_ADD: // User press "Add" button
				if ( $this->FromAlerts ) {
				} elseif ( $bsuser->is_admin() ) {
					$this->row_insert();
				}
				$this->ActionChar = self::BSPAGE_LIST;
				break;
			case self::BSPAGE_EDIT: // User press "Edit" button
				break;
			case self::BSPAGE_UPD: // User press "Update" button
				break;
			case self::BSPAGE_DEL: // User press "Delete" button
				$this->row_delete();
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
			default: // Initial or no action
				break;
		}
		$prefix = $this->preface();
	//	$this->description();
		$prefix2 = $this->preface2();

		return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $bsdriver->platform_start_form( '', "enctype='multipart/form-data' " )
				. "<input type='hidden' name='sc_menu' value='Attachments' /> \n"
				. "<input type='hidden' name='Gobacks' value='" . $this->Gobacks . "'/>\n"
				. "<input type='hidden' name='ID' value='" . $this->DbProcParms[self::PARM_OBJECTIVEID] . "'/>\n"
				. "<input type='hidden' name='P' value='" . $this->DbProcParms[self::PARM_PERIODSTARTING] . "'/>\n"
				. '<b>' . __( 'Objective:' ) . '</b> ' . $this->C_Title . "<br /><br />\n"
				. $prefix
				. $this->upload_dialog()
				. $prefix2
				. $this->ValidationMsg
				. $this->gridview1()
				. $bsdriver->EndForm;
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
		$this->description_text(
			__(
				'The table below lists the objectives assigned to individual departments for the
				current and/or all fiscal years.'
				)
			);
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
					'To change selected objectives, select from <b>Department</b> and from <b>Fiscal
					Year</b>.'
					)
				);
		}
		$this->description_list_end();
		$this->description_end();
	}

	/**
	 * Explain now to use new form by updating class variable.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function new_description()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->setpage_description_head( __( 'Description' ) );
		$this->description_text( __( 'Fill in values and click <b>Save</b>.' ) );
		$this->description_end();
	}

	/**
	 * Setup controls/text after the description.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function preface2()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		// Get the objects title and owner
		$results = $bsdriver->platform_db_query(
			'P_Objective',
			'( %d, %d, %d, %s )',
			array( $bsuser->OrgID, $bsuser->ID, $this->DbProcParms[self::PARM_OBJECTIVEID], 'ignored' )
			);

		$row             = $results[0];
		$this->C_Title   = $row[$bsdriver->Field->C_Title];
		$this->C_OwnerID = $row[$bsdriver->Field->C_OwnerID];

		$new = ( $bsuser->is_admin() )
				? "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . "sc_menu=Attachments&sc_action=new'>New</a>"
				: '';

		$this->GvResults = $bsdriver->platform_db_query( $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );
		$this->RowCount  = count( $this->GvResults );
		$bsdriver->trace_text( ' RowCount=' . $this->RowCount );

		return
			$bsdriver->title_heading( __( 'Attachments' ) )
			. '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . __( 'Rows returned:' ) . ' ' . $this->RowCount . "<br />\n";
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

		if ( $_FILES['file']['name'] == '' ) {
			$this->ValidationMsg = $bsdriver->error_message( __( 'File name is required.' ) );
			$this->UserError     = true;
		} elseif ( $this->UserInput->C_Description == '' ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Descripton is required.' ) );
		} else {
			$fn      = str_replace( '\\', '/', $_FILES['file']['name'] );
			$nodes   = preg_split( '/\//', $fn );
			$fn      = $nodes[count( $nodes ) - 1];
			$results = $bsdriver->platform_db_query(
				'P_DocumentationInsert',
				'( %d, %d, %d, %s, %s, %s, %s )',
				array(
					$bsuser->OrgID, $bsuser->ID,
					$this->DbProcParms[self::PARM_OBJECTIVEID],
					$this->DbProcParms[self::PARM_PERIODSTARTING],
					$fn,
					$this->UserInput->C_Description,
					$_FILES['file']['type'],
					)
			);

			$row = $results[0];

			$this->sFullFilename = $bsuser->UploadFsPath . '\\'
					. $this->DbProcParms[self::PARM_OBJECTIVEID] . '-'
					. $this->DbProcParms[self::PARM_PERIODSTARTING] . '-'
					. str_pad( $row[0], 3, '0', STR_PAD_LEFT ) . '-'
					. $fn;
		$bsdriver->trace_text( '<br/>fullname(' . $this->sFullFilename . ')' );

		move_uploaded_file(
			$_FILES['file']['tmp_name'],
			$this->sFullFilename
			);
		$bsdriver->trace_text( '<br/>tempname(' . $_FILES['file']['tmp_name'] . ')' );
		$bsdriver->trace_text( '<br/>storedas(' . $_FILES['file']['name'] . ')' );
		$bsdriver->trace_text( '<br/>type(' . $_FILES['file']['type'] . ')<br/>' );
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

		if ( $bsuser->is_admin() ) {
			$C_ID = $_GET['ID'];
			if ( !$bsdriver->platform_test_token( $_GET['token'], $C_ID ) ) {
				return $bsdriver->platform_access_denied();
			}
			elseif ( !$this->is_valid_dbinteger( 'ID', $C_ID ) ) {
			} else {
				$this->db_change(
					'P_DocumentationDelete',
					'( %d, %d, %d )',
					array( $bsuser->OrgID, $bsuser->ID, $C_ID ),
					__( 'The attachment has been deleted.' ),
					array()
					);
			}
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
		return '';
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
		return '';
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

		$C_ID             = $row[$bsdriver->Field->C_ID];
		$C_PeriodStarting = $row[$bsdriver->Field->C_PeriodStarting];
		$C_Description    = $row[$bsdriver->Field->C_Description];
		$C_FileName       = $row[$bsdriver->Field->C_FileName];
		$C_MimeType       = $row[$bsdriver->Field->C_MimeType];
		$C_Track_Added    = $row[$bsdriver->Field->C_Track_Added];
		$C_Track_Userid   = $row[$bsdriver->Field->C_Track_Userid];

		// Only admins can delete when row isn't only or in use
		$token        = $bsdriver->platform_get_token( $C_ID );
		$deleteButton = ( $bsuser->is_admin() || $bsuser->Userid == $this->OwnerID )
			? "&nbsp;<a onclick='javascript:return confirm(&#39;" . $this->OnDeleteMsg . "&#39;);' id='DeleteLinkButton_1' href='"
				. $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=delete&token=' . $token . '&ID=' . $this->DbProcParms[self::PARM_OBJECTIVEID]
				. '&P=' . $this->DbProcParms[self::PARM_PERIODSTARTING]
				. '&did=' . $C_ID . "'>" . __( 'Delete' ) . '</a>'
			: '';

		return	ObjtrackerEasyStatic::table_td( '', $C_ID )
				. ObjtrackerEasyStatic::table_td( '', $C_PeriodStarting )
				. ObjtrackerEasyStatic::table_td(
						'', 
						"<a href='" . $bsdriver->PathDownload . 'sc_menu=ShowDocument&token=' . $token . '&ID=' . $C_ID
						. '&mimetype=' . $C_MimeType . '&fname=' . str_replace( ' ', '_', $C_FileName ) . "'>" . $C_FileName
						)
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Description ) )
				. ObjtrackerEasyStatic::table_td( '', $C_Track_Added )
				. ObjtrackerEasyStatic::table_td( '', $C_Track_Userid )
				. ObjtrackerEasyStatic::table_td( '', $deleteButton );
	}

	/**
	 * Page fragment for the upload diaglog.
	 *
	 * @since    1.0
	 * @returns  void      Html segment containing a upload dialog
	 */
	function upload_dialog()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		return	'<b>' . __( 'Attach new document' ) . "</b><br/>\n"
				. ObjtrackerEasyStatic::table_start( "cellspacing='0' id='BsFV1' class='BssFormview' style='left:10px;width:500px;border-collapse:collapse;'" )
				
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', __( '(1)' ) )
				. ObjtrackerEasyStatic::table_td( '', __( 'Browse to file, double click the file name<br />' ) )
				. ObjtrackerEasyStatic::table_tr_end()

				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<input type='file' name='file' id='file'/>" )
				. ObjtrackerEasyStatic::table_tr_end()
			
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', __( '(2)' ) )
				. ObjtrackerEasyStatic::table_td( '', __( 'Enter description:' ) )
			
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td(
						'',
						"<input type='text' name='C_Description' value='" . $this->UserInput->C_Description
						. "' style='width:500px;' maxlength='100' />"
						)
				. ObjtrackerEasyStatic::table_tr_end()
			
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', __( '(3)' ) )
				. ObjtrackerEasyStatic::table_td(
						'',
						__( 'Click <b>Add</b>' ) . '&nbsp;&nbsp;'
						. $bsdriver->Input0SubmitAdd
						)
				. ObjtrackerEasyStatic::table_tr_end()
			
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', __( '(4)' ) )
				. ObjtrackerEasyStatic::table_td( '', __( 'View the file characteristics' ) )
				. ObjtrackerEasyStatic::table_tr_end()
			
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', __( '(5)' ) )
				. ObjtrackerEasyStatic::table_td(
						'',
						__( 'Repeat to add additional files or click' )
						. " <a href='#' onclick=\"javascript:window.history.go(-"
						. $this->Gobacks . ');return false;\"><b> ' . __( 'Go back' ) . '</b></a> '
						. __( 'to return.' )
						)
				. ObjtrackerEasyStatic::table_tr_end()

				. ObjtrackerEasyStatic::table_end() . '<br />' ;
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
}

?>
