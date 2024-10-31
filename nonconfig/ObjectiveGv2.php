<?php
/**
 * Assists Objective.php by managing display and updating of an objective's measurements.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Objective's measurement processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsObjectiveGridView2 extends ObjtrackerConfigPage
{
	/**
	 * Holds the current objective ID
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	public $ObjectiveID;

	/**
	 * Holds the current metric type.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	public $MetricTypeID;

	/**
	 * Holds the current owner ID
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	public $OwnerID;

	/**
	 * Holds the users input
	 *
	 * @var ObjtrackerFormField
	 * @access private
	 * @since 1.0
	 */
	public $UserInput;

	/**
	 * Holds object of the objective definition.
	 *
	 * @var BsObjective
	 * @access private
	 * @since 1.0
	 */
	public $Formview1;

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
			'MeaDelBug'		=> __( 'Error getting PeriodStarting' ),
			'MeaDelAtt'		=> __( 'Objective has attachments can not delete' ),
			);
	}

	/**
	 * Return a form for listing or editing measurement values.
	 *
	 * @since    1.0
	 * @param    object    $gridview3  The missing objects
	 * @return   string                Html fragment
	 */
	public function measurement_response( $gridview3 )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->MetricTypeID = $this->Formview1->MetricTypeID;

		$message = '';
		$this->ActionChar = $bsdriver->Panel == '2' ? substr( $bsdriver->Action, 0, 1 ) : self::BSPAGE_LIST;
		$bsdriver->trace_text( '<br />obj2panel(' . $bsdriver->Panel . ' ' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		// Setup
		if ( isset( $_GET[ 'SortHiddenField' ] ) ) {
			$this->HiddenSortField     = $_GET['SortHiddenField'];
			$this->HiddenSortDirection = $_GET['SortHiddenDirection'];
			$bsdriver->trace_text( 'get hidden Sortfield ' . $this->HiddenSortDirection . ' ' . $this->HiddenSortField );
		} else {
			$this->HiddenSortField     = '' ;
			$this->HiddenSortDirection = '';
		}

		switch ( $this->ActionChar ) {
			case self::BSPAGE_ADD: 		// User press "Add" button
				$this->item_insert();
				break;
			case self::BSPAGE_EDIT: 	// User press "Edit" button
				break;
			case self::BSPAGE_UPD: 		// User press "Update" button
				$this->UserError = false;
				$this->item_update();
				break;
			case self::BSPAGE_DEL: 		// User press "Delete" button
				$this->item_delete();
				break;
			case self::BSPAGE_SORT: 	// User click to sort by column
				$lastSort = $_GET[ 'Ss' ];
				if ( strlen( $lastSort ) > 1 ) {
					$lastField     = substr( $lastSort, 2 );
					$lastDirection = substr( $lastSort, 0, 1 );

					if ( $lastField == $bsdriver->SortField ) {
						$bsdriver->trace_text( ' sort same ' );
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
			default:	// Initial or no action
				break;
		}
		$this->description();
		if ( $bsuser->should_show( ObjtrackerUser::UIShowMeasurements ) ) {
			$this->GvResults = $bsdriver->platform_db_query( $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );
			$this->RowCount  = count( $this->GvResults );

			if ( $this->RowCount == 0 ) {
				return
					"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
					. $this->_Description
					. $this->ValidationMsg
					. ObjtrackerEasyStatic::table_start( "style='width:720px;' class='BssTable'" )
					. ObjtrackerEasyStatic::table_tr_start( '' )
					. ObjtrackerEasyStatic::table_td( "style='width:80%;color:red;'", 'No Measurements recorded' )
					. ObjtrackerEasyStatic::table_td( '', $gridview3->missing_response() )
					. ObjtrackerEasyStatic::table_tr_end()
					. ObjtrackerEasyStatic::table_end();
			} else {
				$instructions = $bsuser->should_show( ObjtrackerUser::UIShowInfo )
					? __( 'To change a measurement, click on <b>Edit</b>, change the value(s), and click on <b>Update</b>.<br />' )
					: '';

				return
					"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
					. $bsdriver->platform_start_form( '', '' )
					. $this->_Description
					. $instructions
					. $this->ValidationMsg
					. ObjtrackerEasyStatic::table_start( "width:780px;margin-left: 0px;' class='BssTable'" )
					. ObjtrackerEasyStatic::table_tr_start( '' )
					. ObjtrackerEasyStatic::table_td(
						"style='width:80%;'",
						ObjtrackerEasyStatic::table_tr_start( '' )
						. ObjtrackerEasyStatic::table_td( '', $this->gridview1() )
						. ObjtrackerEasyStatic::table_td( '', $gridview3->missing_response() )
						. ObjtrackerEasyStatic::table_tr_end()
						)
					. ObjtrackerEasyStatic::table_tr_end()
					. ObjtrackerEasyStatic::table_end()
					. $bsdriver->EndForm;
			}
		} else {
			return $this->_Description;
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

		$bsdriver->PageState = '&p=2&ID=' . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID];

		$this->setpage_description_head( 'Measurements Recorded', ObjtrackerUser::UIShowMeasurements, '' );
		$this->description_end();
	}

	/**
	 * Insert a missing measurement period.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function item_insert()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$bsdriver->trace_text( ' <b>insertID</b>' );
		$ID  = $_GET['ID'];
		$ID2 = $_GET['ID2'];
		$ID2 = str_replace( '/', '-', $ID2 );
		if ( !$bsdriver->platform_test_token( $_GET['token'], $ID ) ) {
			return $bsdriver->platform_access_denied();
		} elseif ( !$this->is_valid_dbinteger( 'ID', $ID ) ) {
		} elseif ( !$this->is_valid_dbparm( 10, 'ID2', $ID2 ) ) {
		} else {
			$bsdriver->trace_text( ' <b>insertID</b>(' . $ID . ')' );
			$bsdriver->trace_text( ' insertPeriod(' . $ID2. ')' );

			$this->db_change(
				'P_MeasurementInsert',
				'( %d, %d, %d, %s, %s, %s )',
				array( $bsuser->OrgID, $bsuser->ID, $ID, $ID2, '', '' ),
				'',
				$this->db_messages
			);
		}
	}

	/**
	 * Validate and update a measurement.
	 *
	 * @since    1.0
	 * @returns  string             Html segment containing display/edit form.
	 */
	function item_update()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;


		$ID2             = $_POST['ID2'];
		$this->UserInput = new ObjtrackerFormField();

		$nv                             = new ObjtrackerMetricValue( $this->MetricTypeID, trim( $_POST['C_Measurement'] ) );
		$this->UserInput->C_Measurement = $nv->normalized();
		$this->UserInput->C_Notes       = trim( $_POST['C_Notes'] );

		if ( $nv->error() != '' ) {
			$this->ValidationMsg = $bsdriver->error_message( __( 'Measurement:' ) . ' ' . $nv->error() );
			$this->UserError     = true;
		} elseif ( !$this->is_valid_dbinteger( 'ID', $ID2 ) ) {
		} else {
			$this->db_change(
				'P_MeasurementUpdate',
				'( %d, %d, %d, %s, %s, %s )',
				array(
					$bsuser->OrgID, $bsuser->ID,
					$ID2, 'ignore', $this->UserInput->C_Measurement, $this->UserInput->C_Notes,
					),
				'',
				$this->db_messages
			);
		}
	}


	/**
	 * Validate and delete measurement..
	 *
	 * @since    1.0
	 * @returns  void.
	 */
	function item_delete()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$ID2 = $_GET['ID2'];
		if ( !$bsdriver->platform_test_token( $_GET['token'], $ID2 ) ) {
			return $bsdriver->platform_access_denied();
		} elseif ( !$this->is_valid_dbinteger( 'ID', $ID2 ) ) {
		} else {
			$this->db_change(
				'P_MeasurementDelete',
				'( %d, %d, %d )',
				array( $bsuser->OrgID, $bsuser->ID, $ID2 ),
				'',
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
	function showrow_edit_this( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID             = $row[$bsdriver->Field->C_ID];
		$C_Docs1Key       = $row[$bsdriver->Field->C_Docs1Key];
		$C_Status         = $row[$bsdriver->Field->C_Status];
		$C_Docs           = $row[$bsdriver->Field->C_Docs];
		$C_PeriodStarting = substr( $row[$bsdriver->Field->C_PeriodStarting], 0, 10 );
		$C_Measurement    = $row[$bsdriver->Field->C_Measurement];
		$C_Notes          = $row[$bsdriver->Field->C_Notes];
		$C_Track_Changed  = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid   = $row[$bsdriver->Field->C_Track_Userid];

		$cancelButton = $bsdriver->Input2SubmitCancel;
		return	'<td>' . $C_ID
				. "<input type='hidden' name='ID' value='" . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] . "' />"
				. "<input type='hidden' name='ID2' value='" . $C_ID . "' />\n"
				. "<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' /></td> \n"
				. "<td><img src='" . $bsdriver->PathImages . 'Status' . $C_Status . ".png' alt='HTML tutorial'></td>\n"
				. '<td>' . $C_Docs . "</td>\n"
				. '<td>' . stripslashes( $C_PeriodStarting ) . "</td>\n"
				. "<td><input type='text' name='C_Measurement' value='" . $C_Measurement . "' /></td>\n"
				. "<td><input type='text' name='C_Notes' value='" . $C_Notes . "' /></td>\n"
				. '<td>' . stripslashes( $C_Track_Changed ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Userid ) . "</td>\n"
				. "<td>\n"
				. $bsdriver->Input2SubmitUpdate
				. $cancelButton
				. "</td>\n";
	}

	/**
	 * Return row when the user had an edit error on this row.
	 *
	 * @since    1.0
	 * @params   array	$row        Row array of data
	 * @returns  string             Html segment containing display/edit form.
	 */
	function showrow_update_this( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$ID2              = $_POST['ID2'];
		$C_Status         = $row[$bsdriver->Field->C_Status];
		$C_Docs           = $row[$bsdriver->Field->C_Docs];
		$C_PeriodStarting = substr( $row[$bsdriver->Field->C_PeriodStarting], 0, 10 );
		$C_Track_Changed  = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid   = $row[$bsdriver->Field->C_Track_Userid];

		$usageLink = ( $C_Docs == 0 )
			? __( 'None' )
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Usage&Table=T_Person&Column=DepartmentID&Value='
			. $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] . "'>" . __( 'Yes:' ) . $C_Docs . '</a>';

		$cancelButton = $bsdriver->Input2SubmitCancel;

		return	'<td>' . $ID2
				. "<input type='hidden' name='ID' value='" . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] . "' />\n"
				. "<input type='hidden' name='ID2' value='" . $ID2 . "' />\n"
				. "<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' /></td>\n"
				. "<td><img src='" . $bsdriver->PathImages . 'Status' . $C_Status . ".png' alt='HTML tutorial'></td>\n"
				. '<td>' . stripslashes( $usageLink ) . "</td>\n"
				. '<td>' . stripslashes( $C_PeriodStarting ) . "</td>\n"
				. "<td><input type='text' name='C_Measurement' value='" . $this->UserInput->C_Measurement . "' /></td>\n"
				. "<td><input type='text' name='C_Notes' value='" . $this->UserInput->C_Notes . "' /></td>\n"
				. '<td>' . stripslashes( $C_Track_Changed ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Userid ) . "</td>\n"
				. "<td>\n"
				. $bsdriver->Input2SubmitUpdate
				. $cancelButton
				. "</td>\n";
	}

	/**
	 * Return row when the item is not being updated.
	 *
	 * @since    1.0
	 * @params   array	$row        Row array of data
	 * @returns  string             Html segment containing display/edit form.
	 */
	function showrow_other( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID             = $row[$bsdriver->Field->C_ID];
		$C_Status         = $row[$bsdriver->Field->C_Status];
		$C_Docs           = $row[$bsdriver->Field->C_Docs];
		$C_PeriodStarting = substr( $row[$bsdriver->Field->C_PeriodStarting], 0, 10 );
		$C_Measurement    = $row[$bsdriver->Field->C_Measurement];
		$C_Notes          = $row[$bsdriver->Field->C_Notes];
		$C_Track_Changed  = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid   = $row[$bsdriver->Field->C_Track_Userid];

		$usageLink = ( $C_Docs == 0 )
			? __( 'None' )
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Usage&Table=T_Person&Column=DepartmentID&Value='
				. $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] . "'>" . __( 'Yes:' ) . $C_Docs . '</a>';
		return	'<td>' . stripslashes( $C_ID ) . "</td>\n"
				. "<td><img src='" . $bsdriver->PathImages . 'Status' . $C_Status . ".png' alt='HTML tutorial'></td>\n"
				. '<td>' . stripslashes( $usageLink ) . "</td>\n"
				. '<td>' . stripslashes( $C_PeriodStarting ) . "</td>\n"
				. '<td>' . stripslashes( $C_Measurement ) . "</td>\n"
				. '<td>' . stripslashes( $C_Notes ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Changed ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Userid ) . "</td>\n"
				. " <td>&nbsp;</td>\n";
	}


	/**
	 * Return row when the item only being listed.
	 *
	 * @since    1.0
	 * @params   array	$row        Row array of data
	 * @returns  string             Html segment containing display/edit form.
	 */
	function showrow_list( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID             = $row[$bsdriver->Field->C_ID];
		$C_Status         = $row[$bsdriver->Field->C_Status];
		$C_Docs           = $row[$bsdriver->Field->C_Docs];
		$C_PeriodStarting = substr( $row[$bsdriver->Field->C_PeriodStarting], 0, 10 );
		$C_Measurement    = $row[$bsdriver->Field->C_Measurement];
		$C_Notes          = $row[$bsdriver->Field->C_Notes];
		$C_Track_Changed  = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid   = $row[$bsdriver->Field->C_Track_Userid];

		// Only admins can edit
		$editButton = ( !$bsuser->is_admin() )
			? ''
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
			. $bsdriver->MenuName . '&p=2&sc_action=edit&ID=' . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID]
			. '&ID2=' . $C_ID . "'>" . __( 'Edit' ) . '</a>';

		// Only admins can delete when row isn't only or in use
		$token        = $bsdriver->platform_get_token( $C_ID );
		$deleteButton = ( !$bsuser->is_admin() )
			? ''
			: "&nbsp;<a onclick='javascript:return confirm(&#39;" . $this->OnDeleteMsg . "&#39;);' id='DeleteLinkButton_1' href='"
				. $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&p=2&sc_action=delete&token=' . $token . '&ID=' . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID]
				. '&ID2=' . $C_ID . "'>" . 'Delete' . '</a>';

		// Make usage more readable and a bigger target for clicking
		$docs = ( $C_Docs == 0 ) ? __( 'None' ) : __( 'Yes:' ) . $C_Docs;

		return	'<td>' . stripslashes( $C_ID ) . "</td>\n"
				. "<td><img src='" . $bsdriver->PathImages . 'Status' . $C_Status . ".png' alt='HTML tutorial'></td>\n"
				. "<td><a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Attachments&ID='
					. $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID]
					. '&P=' . $C_PeriodStarting . "'>" . $docs . "</a></td>\n"
				. '<td>' . stripslashes( $C_PeriodStarting ) . "</td>\n"
				. "<td style='text-align:right'>" . stripslashes( $C_Measurement ) . "</td>\n"
				. '<td>' . stripslashes( $C_Notes ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Changed ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Userid ) . "</td>\n"
				. '<td>' . $editButton . '' . $deleteButton . "</td>\n";
	}

	/**
	 * Return a gridview for display.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing a gridview
	 */
	function gridview1()
	{
		$bsdriver = $this->bsdriver;

		$this->on_db_results();

		// If sorting, sort the data, and sort sort state in hidden fields
		if ( strlen( $this->SortDirection ) > 0 ) {
			$bsdriver->trace_text( 'sorting on ' . $this->SortDirection . ' ' . $bsdriver->SortField );
			$this->GvResults = objtracker_dataset_sort( $this->GvResults, $bsdriver->SortField, $this->SortDirection );
			$bsdriver->trace_text( 'HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );

			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='" . $bsdriver->SortField . "' />\n"
				. "<input type='hidden' name='SortHiddenDirection' value='" . $this->SortDirection . "' />\n";
			$sortUrl      = '&Ss=' . $this->SortDirection . ':' . $bsdriver->SortField;
		} else {
			$bsdriver->trace_text( 'No sorting, HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );
			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='' />\n"
				. "<input type='hidden' name='SortHiddenDirection' value='' />\n";
			$sortUrl      = '&Ss=';
		}
		$hiddenValues .= "<input type='hidden' name='HiddenObjectiveID' value='"
						. $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] . "' />\n";

		// Write each row of gridview
		$tableBody  = '';
		$primaryRow = true;
		foreach ( $this->GvResults as $row ) {
			$tableBody .= $primaryRow ? "<tr class='BssGvOddRow'>" : "<tr class='BssGvEvenRow'>";

			$primaryRow = $primaryRow ? false : true;
			$C_ID       = $row[$bsdriver->Field->C_ID];

			if ( $this->ActionChar == self::BSPAGE_EDIT && $C_ID == $_GET['ID2'] ) {
				$bsdriver->trace_text( ' showrow_edit_this' );
				$tableBody .= $this->showrow_edit_this( $row );
			} elseif ( $this->ActionChar == self::BSPAGE_UPD && $C_ID == $_POST['ID2'] ) {
				$tableBody .= $this->showrow_update_this( $row );
				// phpcs doesn't like next elseif if these two comments removed! 
				// -- ERROR | Opening brace should be on the same line as the declaration
			} elseif ( $this->ActionChar == self::BSPAGE_EDIT || $this->ActionChar == self::BSPAGE_UPD ) {
				$tableBody .= $this->showrow_other( $row );
			} else {
				$tableBody .= $this->showrow_list( $row );
			}

			$tableBody .= "</tr> <!-- close Scorecard-Gridview-Row --> \n";
		}

		return	$hiddenValues
				. "<table class='BssGridview'> <!-- Gridview-Table -->\n"
				. $bsdriver->sort_headers( $sortUrl, $this->GvColumns )
				. $tableBody
				. "</table> <!-- close Scorecard-Gridview-Table --> \n"; // Close gridview and form
	}
}

?>
