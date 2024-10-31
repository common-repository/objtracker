<?php
/**
 * Present current user's objectives that require measurement results.
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
function bs_alerts( $bsdriver, $bsuser )
{
	$configPage = new BsAlertsPage(
		$bsdriver,
		$bsuser,
		__( "Table's Columns" ),	// Parm: 3 - Title
		array(
				new ObjtrackerGvColumn( __( 'ID' ), D_Column::C_ID ),
				new ObjtrackerGvColumn( __( 'Status' ), D_Column::C_Status ),
				new ObjtrackerGvColumn( '', D_Column::C_Action ),
				new ObjtrackerGvColumn( __( 'Category' ), D_Column::C_Type ),
				new ObjtrackerGvColumn( __( 'Frequency' ), D_Column::C_Frequency ),
				new ObjtrackerGvColumn( __( 'Starting' ), D_Column::C_PeriodStarting ),
				new ObjtrackerGvColumn( __( 'Objective' ), D_Column::C_Title ),
			),
		'P_Alerts',
		'( %d, %d, %d )',
		'dummy',
		'Are you sure you want to delete this objective?'
		);
	return $configPage->Response();
}

/**
 * Alerts processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsAlertsPage extends ObjtrackerConfigPage
{
	const PARM_USERID = 2;

	/**
	 * Holds the filtered measurements of all objectives
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $Objectives;
	/**
	 * Holds the filtered measurements arranged for displaying in gridview
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $DataTable;
	/**
	 * Holds the objective ID for the measurement dialog
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $ObjectiveID;
	/**
	 * Holds the date that the measurement is for.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $PeriodStarting;
	/**
	 * Holds the metric type for the measurement diaglog.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $MetricTypeID;
	/**
	 * Holds the formview (objective info) for measurement diaglog
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $FvResultsRow;

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

		if ( !$bsuser->is_admin() ) {
			$this->DbProcParms   = array( $bsuser->OrgID, $bsuser->ID, $bsuser->ID );
			$bsdriver->PageState = '';
		}
		else {
			if ( isset( $_GET['Set']) ) {
				$set = $_GET['Set'];
			}
			else {
				$set = 'All';
			}
			if ( $set == 'All' ) {
				$this->DbProcParms   = array( $bsuser->OrgID, $bsuser->ID, 0 );
				$bsdriver->PageState = '&Set=All';
			}
			else {
				$this->DbProcParms   = array( $bsuser->OrgID, $bsuser->ID, $bsuser->ID );
				$bsdriver->PageState = '&Set=My';
			}
		}

		$bsdriver->trace_text( ' Ui' . $bsuser->UiSettings );
		// Setup
		if ( isset( $_GET[ 'SortHiddenField' ] ) ) {
			$this->HiddenSortField     = $_GET['SortHiddenField'];
			$this->HiddenSortDirection = $_GET['SortHiddenDirection'];
			$bsdriver->trace_text( 'get hidden Sortfield ' . $this->HiddenSortDirection . ' ' . $this->HiddenSortField );
		}
		else {
			$this->HiddenSortField     = '' ;
			$this->HiddenSortDirection = '';
		}
		$bsdriver->trace_text( 'hidden(' . $this->HiddenSortDirection . $this->HiddenSortField . ')' );

		$message = '';
		$this->ActionChar = substr( $bsdriver->Action, 0, 1 );
		$bsdriver->trace_text( ' action(' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		switch ( $this->ActionChar ) {
			case self::BSPAGE_ADD: // User press "Add" button
				$this->ObjectiveID    = $_POST['ID'];
				$this->PeriodStarting = $_POST['ps'];
				$this->UserInput      = new ObjtrackerFormField();
				$this->get_objective();
				$this->row_insert();
				if ( $this->UserError ) {
					return $this->row_new();
				}
				if ( $bsdriver->Panel == '0' ) {
					$this->ActionChar = self::BSPAGE_LIST;
				}
				else {
					include OBJTRACKER_NON2NON_DIR . 'Attachments' . OBJTRACKER_CONTENT_MTYPE;
					return bs_attachments( $bsdriver, $bsuser, $this->ObjectiveID, $this->PeriodStarting );
				}
				break;
			case self::BSPAGE_EDIT: // User press "Edit" button
				break;
			case self::BSPAGE_UPD: // User press "Update" button
				break;
			case self::BSPAGE_NEW: // User press "New" button
				$this->UserInput      = new ObjtrackerFormField();
				$this->ObjectiveID    = $_GET['ID'];
				$this->PeriodStarting = $_GET['ps'];
				$this->get_objective();
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
					}
					else {
						$bsdriver->trace_text( ' sort new' );
						$this->SortDirection = 'A';
					}
				}
				else {
					$bsdriver->trace_text( ' sort 1st' );
					$this->SortDirection = 'A';
				}
				break;
			case self::BSPAGE_LIST:
				break;
			default:	// Initial or no action
				break;
		}
		$prefix = $this->preface();
		$this->description();
		$this->gather_data();
		$this->setup_dataset();

		$rowsReturned = count( $this->DataTable ) == 0
			? '&nbsp;&nbsp;' . __( 'No rows returned' ) . "<br />\n"
			: __( 'Count:' ) . ' ' . ' ' . count( $this->DataTable ) . "<br />\n";

		return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $prefix
				. $this->_Description
				. $bsdriver->title_heading( __( 'Alerts' ) )
				. $bsdriver->platform_start_form( '', '' )
				. " <input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />\n"
				. $this->preface2()
				. $bsdriver->EndForm
				. $rowsReturned
				. $this->gridview1()
				. $this->trailer();
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

		$bsdriver->trace_text( ' <br />PageState=' . $bsdriver->PageState );
		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text(
				__(
				'The table below lists your assigned objectives that require
				a measurement value to indicate the current status of the objective.'
				)
			);
		$this->description_list_start();
		$this->description_list_item( __( 'To view the definition of the status icon, hover the mouse over the icon.' ) );
		$this->description_list_item(
				__(
				'To sort the table, click on <b>ID</b>, <b>Status</b>, <b>Category</b>, <b>Frequency</b>, '
				. '<b>Starting</b>, or <b>Objective</b>.'
				)
			);
		$this->description_list_item(
				__(
				'To add or update a measurement, click on <b>Add</b> or <b>Revise</b> for the objective,
				enter the measurement, and click on <b>Save.</b>.'
				)
			);
		$this->description_list_end();
		$this->description_end();
	}

	/**
	 * Explain now to use page by updating class variable.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function new_description()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
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

		if ( !$bsuser->is_admin() ) {
			return '';
		}
		else {
			if ( isset( $_GET['Set'] ) ) {
				$set = $_GET['Set'];
			}
			else {
				$set = 'All';
			}
			if ( $this->DbProcParms[self::PARM_USERID] == 0 ) {
				$checked1 = "checked='checked'";
				$checked2 = '';
			}
			else {
				$checked1 = '';
				$checked2 = "checked='checked'";
			}

			$radioButtons =	"<b>Choose:</b>\n"
				. " <input type='radio' name='Radio1' value='All' " . $checked1
				. " onclick=\"document.location='" . $bsdriver->PlatformParm . "sc_menu=Alerts&Set=All'\" />\n"
				. " <b>All Alerts</b> \n"
				. " <input type='radio' name='Radio1' value='My' " . $checked2
				. " onclick=\"document.location='" . $bsdriver->PlatformParm . "sc_menu=Alerts&Set=My'\" />\n"
				. " <b>Your Alerts</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
		}

		return $radioButtons;
	}
	/**
	 * Retrieve objective from database.
	 *
	 * @since    1.0
	 */
	function get_objective()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$results = $bsdriver->platform_db_query(
				'P_AlertPrompt',
				'( %d, %d, %d, %s )',
				array( $bsuser->OrgID, $bsuser->ID, $this->ObjectiveID, $this->PeriodStarting )
				);
		if ( count( $results ) == 1 ) {
			$this->FvResultsRow = $results[0];
			$bsdriver->trace_text( ' RowCount=' . count( $this->FvResultsRow ) );

			$this->UserInput->C_Measurement = $this->FvResultsRow[$bsdriver->Field->C_Measurement];
			$this->UserInput->C_Notes = $this->FvResultsRow[$bsdriver->Field->C_Notes];
			$this->MetricTypeID       = $this->FvResultsRow[$bsdriver->Field->C_MetricTypeID];
		}
		else {
			$this->ValidationMsg = $bsdriver->error_message( __( 'Fatal error retrieving objective' ) );
			$this->UserError     = true;
			$bsdriver->trace_text( ' ID=' . $this->ObjectiveID );
			$bsdriver->trace_text( ' ps=' . $this->PeriodStarting );
		}
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

		$nv                             = new ObjtrackerMetricValue( $this->MetricTypeID, trim( $_POST['C_Measurement'] ) );
		$this->UserInput->C_Measurement = $nv->normalized();
		$this->UserInput->C_Notes       = trim( $_POST['C_Notes'] );

		if ( $nv->error() != '' ) {
			$this->ValidationMsg = $bsdriver->error_message( __( 'Measurement:' ) . ' ' . $nv->error() );
			$this->UserError     = true;
		}
		elseif ( !$this->is_valid_dbinteger( 'ID', $this->ObjectiveID ) ) {
		}
		else {
			$this->db_change(
				'P_MeasurementInsert',
				'( %d, %d, %d, %s, %s, %s )',
				array(
					$bsuser->OrgID, $bsuser->ID, $this->ObjectiveID,
					$this->PeriodStarting,
					$this->UserInput->C_Measurement,
					$this->UserInput->C_Notes,
					),
				__( 'The measurement has been saved.' ),
				array()
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

		$this->new_description();

		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$action           = $this->FvResultsRow[$bsdriver->Field->C_Measurement] == '' ? __( 'Add' ) : __( 'Revise' );
		$C_Type           = $this->FvResultsRow[$bsdriver->Field->C_Type];
		$C_Title          = $this->FvResultsRow[$bsdriver->Field->C_Title];
		$C_Description    = $this->FvResultsRow[$bsdriver->Field->C_Description];
		$C_Source         = $this->FvResultsRow[$bsdriver->Field->C_Source];
		$C_Target         = $this->FvResultsRow[$bsdriver->Field->C_Target];
		$C_MetricType     = $this->FvResultsRow[$bsdriver->Field->C_MetricType];
		$C_MetricTypeDesc = $this->FvResultsRow[$bsdriver->Field->C_MetricTypeDesc];

		return "\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $bsdriver->platform_start_form( '', '' )
				. "<input type='hidden' name='ID' value='" . $this->ObjectiveID . "' />\n"
				. "<input type='hidden' name='ps' value='" . $this->PeriodStarting . "' />\n"
				. "<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' /> \n"
				. $this->_Description
				. $txt = $bsdriver->description_headertext( __( 'Measurement Entry' ) )
				. $this->ValidationMsg
				. ObjtrackerEasyStatic::table_start( "cellspacing='0' id='BsFV1' class='BssFormview' style='width:500px;border-collapse:collapse;'" )
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "colspan='6'", '<b>' . $action . ' ' . __( 'measurement for period starting:' ) . ' ' . $this->PeriodStarting . '</b>' )
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:20%'", '<b>' . __( 'Category' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( "colspan='5'", $C_Type )
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Title' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( "colspan='5'", $C_Title )
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Description' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( "colspan='5'", $C_Description )
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Source' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( "colspan='5'", $C_Source )
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "colspan='6'", '<hr />' )
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:20%'", '<b>' . __( 'Target' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $C_Target )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Type:' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $C_MetricType )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Format:' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $C_MetricTypeDesc )
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Measurement' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td(
					"colspan='5'",
					"<input type='text' name='C_Measurement' style='width:80px;' maxlength='12' value='" . $this->UserInput->C_Measurement . "'/>"
					)
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Notes' ) . '</b><br />Use Shift+Enter for newline.' )
				. ObjtrackerEasyStatic::table_td(
						"colspan='5'",
						"<textarea name='C_Notes' rows='2' cols='60' maxlength='128'" . $this->UserInput->C_Notes . "'></textarea>"
						)
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '&nbsp' )
				. ObjtrackerEasyStatic::table_td(
					"colspan='5' style='text-align: center'",
					$bsdriver->Input0SubmitSave
					. $bsdriver->Input1SubmitSave
					. $bsdriver->Input0SubmitCancel
					)
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_end()
				. $bsdriver->EndForm;
	}

	/**
	 * Retrieve measurement data, filtering for last of year.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function gather_data()
	{
		$bsdriver = $this->bsdriver;

		$this->GvResults = $bsdriver->platform_db_query(
				$this->DbProcList,
				$this->DbProcArgs,
				$this->DbProcParms
				);

		$this->Objectives = array();
		$o                = null;
		$sLastOID         = '';
		$skipToNext       = false;
		$gather           = false;

		// All periods represented for each objective.
		// Capture last with value or if none, the null row.

		foreach ( $this->GvResults as $row ) {
			$C_OID            = $row[$bsdriver->Field->C_OID];
			$C_MID            = $row[$bsdriver->Field->C_MID];
			$C_PeriodStarting = $row[$bsdriver->Field->C_PeriodStarting];

			if ( $sLastOID != $C_OID ) {
				// on new object, grab it

				$o = new ObjtrackerMeasuredObjective();
				array_push( $this->Objectives, $o );
				//
				$gather     = true;
				$skipToNext = $C_MID == '' ? true : false;
			}
			elseif ( !$skipToNext && $C_MID == '' ) {
				// existing object has null measurement
				$skipToNext = true;
				$gather     = false;
			}
			else {
				// existing object has measurement
				$gather = true;
			}
			if ( $gather ) {
				// Set or overlay object with same OID key
				$o->sOID    = $C_OID;
				$o->sMID    = $C_MID;
				$o->sType   = $row[$bsdriver->Field->C_Type];
				$o->sTitle  = $row[$bsdriver->Field->C_Title];
				$o->sStatus = $row[$bsdriver->Field->C_Status];

				$o->cFrequencyID  = $row[$bsdriver->Field->C_FrequencyID];
				$o->sFrequency    = $row[$bsdriver->Field->C_Frequency];
				$o->cMetricTypeID = $row[$bsdriver->Field->C_MetricTypeID];
				$o->sTarget       = $row[$bsdriver->Field->C_Target];
				$o->sTarget1      = $row[$bsdriver->Field->C_Target1];
				$o->sTarget2      = $row[$bsdriver->Field->C_Target2];
				$o->sFiscalYear1  = $row[$bsdriver->Field->C_Fy1Title];
				$o->sFiscalYear2  = $row[$bsdriver->Field->C_Fy2Title];
				$o->sMeasurement  = $row[$bsdriver->Field->C_Measurement];
				$o->sNotes        = $row[$bsdriver->Field->C_Notes];
				$o->sAction       = $row[$bsdriver->Field->C_Action];
				if ( $o->sMeasurement == __( 'Missing' ) ) {
					$o->sMeasurement = '';
					$o->sAction      = 'Add';
				}
				$o->dPeriodStarting = substr( $C_PeriodStarting, 0, 10 );
			}
			$sLastOID = $C_OID;
		}

	}

	/**
	 * Using filtered metric data, create a structure similar to a database response.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function setup_dataset()
	{
		$bsdriver = $this->bsdriver;

		$this->DataTable = array();

		// Fill data rows with data
		$lastOID     = '';
		$lastDataRow = null;

		foreach ( $this->Objectives as $measure ) {
			if ( $lastOID == '' || $lastOID != $measure->sOID ) {
				$row                             = array();
				$row[D_Column::C_ID]             = $measure->sOID;
				$row[D_Column::C_Type]           = $measure->sType;
				$row[D_Column::C_Title]          = $measure->sTitle;
				$row[D_Column::C_Action]         = $measure->sAction;
				$row[D_Column::C_Frequency]      = $measure->sFrequency;
				$row[D_Column::C_PeriodStarting] = $measure->dPeriodStarting;

				$row[D_Column::C_Status]    = ObjtrackerEasyStatic::get_statusurl( $measure->sStatus );
				$row[D_Column::C_PopUpText] = ObjtrackerEasyStatic::hovertext_by_status( $measure->sStatus );
				array_push( $this->DataTable, $row );
				$lastDataRow = $row;
			}
			elseif ( $measure->sMeasurement != null && $measure->sMeasurement != '' ) {
				$lastDataRow[D_Column::C_Measurement] = $measure->sMeasurement;
			}
			$lastOID = $measure->sOID;
		}
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
			$this->DataTable = objtracker_dataset_sort( $this->DataTable, $bsdriver->SortField, $this->SortDirection );
			$bsdriver->trace_text( 'HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );

			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='" . $bsdriver->SortField . "' />\n"
				. "<input type='hidden' name='SortHiddenDirection' value='" . $this->SortDirection . "' />\n";
			$sortUrl      = '&Ss=' . $this->SortDirection . ':' . $bsdriver->SortField;
		}
		else {
			$bsdriver->trace_text( 'No sorting, HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );
			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='' />\n"
				. "<input type='hidden' name='SortHiddenDirection' value='' />\n";
			$sortUrl      = '&Ss=';
		}

		// Write each row of gridview
		$tableBody  = '';
		$primaryRow = true;
		$id         = 0;
		foreach ( $this->DataTable as $row ) {
			$id++;
			$tableBody .= $primaryRow ? ObjtrackerEasyStatic::table_tr_start( "class='BssGvOddRow'" ) : ObjtrackerEasyStatic::table_tr_start( "class='BssGvEvenRow'" );
			$primaryRow = $primaryRow ? false : true;

			$tableBody .= $this->gridview_row_list( $row, $id );

			$tableBody .= ObjtrackerEasyStatic::table_tr_end();
		}

		return	$hiddenValues
				. ObjtrackerEasyStatic::table_start( "class='BssGridview'" )
				. $this->sort_headers( $sortUrl )
				. $tableBody
				. ObjtrackerEasyStatic::table_end();
	}

	/**
	 * Return the column headers for a gridview.
	 *
	 * @since    1.0
	 * @params   string  $sortStatus   String for holding sort state
	 * @returns  string                Html segment containing a gridview header
	 */
	public function sort_headers( $sortStatus )
	{
		$bsdriver = $this->bsdriver;

		$headerText = '';
		foreach ( $this->GvColumns as $gvColumn ) {
			$dbColumnNumber = $gvColumn->Title == 'ID' ? 0 : $gvColumn->DbColumnName;

			$headerText .= ObjtrackerEasyStatic::table_th(
						'',
						"<a href='" . $bsdriver->PathBase
						. $bsdriver->PlatformParm . 'sc_menu=' . $bsdriver->MenuName . $bsdriver->PageState
						. '&sc_action=sort&fld=' . $dbColumnNumber . $sortStatus . "'>" . $gvColumn->Title . '</a>'
						);
		}

		return
			ObjtrackerEasyStatic::table_tr_start( "class='BssGvHeader'" )
			. $headerText
			. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return a row of a gridview that is only being displayed.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @params   $id         Id number of the row
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_list( $row, $id )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_OID            = $row[D_Column::C_ID];
		$C_Status         = $row[D_Column::C_Status];
		$C_Type           = $row[D_Column::C_Type];
		$C_Action         = $row[D_Column::C_Action];
		$C_Frequency      = $row[D_Column::C_Frequency];
		$C_PeriodStarting = $row[D_Column::C_PeriodStarting];
		$C_Title          = $row[D_Column::C_Title];
		$C_PopUpText      = $row[D_Column::C_PopUpText];

		return	ObjtrackerEasyStatic::table_td( '', $C_OID )
				. ObjtrackerEasyStatic::table_td( '', "<img class='BssButton' src='" . $bsdriver->PathImages . $C_Status . "' title='" . $C_PopUpText . "' />" )
				. ObjtrackerEasyStatic::table_td(
					'',
					"<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Alerts&ID='
						. $C_OID . '&ps=' . $C_PeriodStarting . "&sc_action=new'>" . $C_Action
					)
				. ObjtrackerEasyStatic::table_td( '', $C_Type )
				. ObjtrackerEasyStatic::table_td( '', $C_Frequency )
				. ObjtrackerEasyStatic::table_td( '', $C_PeriodStarting )
				. ObjtrackerEasyStatic::table_td( '', $C_Title );
	}
}

/**
 * Define constants for the column numbers of alert grid view..
 *
 * @package objtracker
 * @category Interface
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
interface D_Column {
	const C_ID             = 0;
	const C_Status         = 1;
	const C_PopUpText      = 2;
	const C_Action         = 3;
	const C_Type           = 4;
	const C_Frequency      = 5;
	const C_PeriodStarting = 6;
	const C_Title          = 7;
}
?>
