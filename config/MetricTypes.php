<?php
/**
 * Administrator's page for managing name and hits for metric types.
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
function bs_metrictypes( $bsdriver, $bsuser )
{
	$configPage = new BsMetricTypesPage(
		$bsdriver,
		$bsuser,
		__( 'Metric Types' ),		// Parm: Title
		array(				// Parm: Gridview columns
				new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
				new ObjtrackerGvColumn( __( 'Objectives' ), 'C_Usage' ),
				new ObjtrackerGvColumn( __( 'Title' ), 'C_Title' ),
				new ObjtrackerGvColumn( __( 'Description' ), 'C_Description' ),
				new ObjtrackerGvColumn( __( 'Changed' ), 'C_Track_Changed' ),
				new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
				new ObjtrackerGvColumn( '', '' ),
			),
		'P_MetricTypeList',
		'( %d, %d )',
		array( $bsuser->OrgID, $bsuser->ID ),
		__( 'Are you sure you want to delete this metric type?' )
		);
	return $configPage->Response();
}

/**
 * Metric type processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsMetricTypesPage extends ObjtrackerConfigPage
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
			'MetUpdTitle'	=> __( 'Title is required' ),
			'MetUpdDesc'	=> __( 'Description is required' ),
			'MetUpdDup'		=> __( 'Title already exists' ),
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
		$this->description_text( __( 'The table below lists the metrics that are assigned to objectives.' ) );
		$this->description_list_start();
		$this->description_list_item(
			__(
				'To sort the table, click on <b>ID</b>, <b>Objectives</b>, <b>Title</b>, <b>Description</b>,
				<b>Changed</b>, or <b>By</b>.'
				)
			);
		if ( $bsuser->is_admin() ) {
			$this->description_list_item(
			__(
				'To edit a metric type, click on <b>Edit</b>, change values, and click on <b>Update</b>.'
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
		$this->_Description .= $bsdriver->title_heading( __( 'Metric Types' ) );
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
			return $bsdriver->extract_link( __( 'Spreadsheet Download' ), '' );
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
		$this->bsdriver->error_message( __( 'Insert is not allowed' ) );
		$this->bsdriver->Action = 'list';
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

		$C_ID          = $_POST['C_ID'];
		$C_Title       = trim( $_POST['C_Title'] );
		$C_Description = trim( $_POST['C_Description'] );

		if ( !$this->is_valid_dbchar( 'ID', $C_ID ) ) {
		} elseif ( !$this->is_valid_dbparm( 64, __( 'Title' ), $C_Title ) ) {
		} elseif ( '$nn,nnn,nnn' != $C_Description && !$this->is_valid_dbparm( 128, __( 'Descrition' ), $C_Description ) ) {
		} else {
			$this->db_change(
				'P_MetricTypeUpdate',
				'( %d, %d, %s, %s, %s )',
				array( $bsuser->OrgID, $bsuser->ID, $C_ID, $C_Title, $C_Description ),
				__( 'The metric type has been updated.' ),
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
		$this->bsdriver->error_message( __( 'Delete is not allowed' ) );
		$this->bsdriver->Action = 'list';
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
		$C_Description   = $row[$bsdriver->Field->C_Description];
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
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Description' value='" . stripslashes( $C_Description ) . "' />" )
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
		$C_Description   = $_POST['C_Description'];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		return	ObjtrackerEasyStatic::table_td(
					'',
					$C_ID . "<input type='hidden' name='C_ID' value='" . $C_ID . "' />"
						. "	<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />"
					)
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Title' value='" . stripslashes( $C_Title ) . "' />" )
				. ObjtrackerEasyStatic::table_td( '', "<input type='text' name='C_Description' value='" . stripslashes( $C_Description ) . "' />" )
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
		$C_Description   = $row[$bsdriver->Field->C_Description];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		return	ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Description ) )
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
		$C_Description   = $row[$bsdriver->Field->C_Description];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		// Only admins can edit
		$editButton = ( !$bsuser->is_admin() )
			? ''
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=edit&id=' . $C_ID . "'>" . __( 'Edit' ) . '</a>';

		// Make usage more readable and a bigger target for clicking
		$usageLink = ( $C_Usage == 0 )
			? __( 'None' )
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm
				. 'sc_menu=Usage&Table=T_Objective&Column=MetricTypeID&Value=' . $C_ID . "'>" . __( 'Yes:' ) . $C_Usage . '</a>';

		return	ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $usageLink ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Description ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) )
				. ObjtrackerEasyStatic::table_td( '', $editButton );
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
