<?php
/**
 * Class used by Dashboard.php to report on targets verus measured results for a single year.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * ObjtrackerDashboard class supports Dashboard.php..
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerDashboard
{
	const _C_ID               = 0;
	const _C_Status           = 1;
	const _C_Category         = 2;
	const _C_Dept             = 3;
	const _C_Title            = 4;
	const _C_Target           = 5;
	const _C_Measurement      = 6;
	const _C_PopUpText        = 7;
	const _C_Sort_Target      = 8;
	const _C_Sort_Measurement = 9;
	
/**
	 * Holds the filtered measured objects.
	 *
	 * @var array of ObjtrackerMeasuredObjective
	 * @access private
	 * @since 1.0
	 */
	private $Objectives;

	/**
	 * Holds the data table for creating a grid view.
	 *
	 * @var array
	 * @access public
	 * @since 1.0
	 */
	public $DataTable;

	/**
	 * Holds the raw data from database for creating a grid view.
	 *
	 * @var array
	 * @access public
	 * @since 1.0
	 */
	private $DbResults;

	/**
	 * Holds array of strings used as column headers in the grid view.
	 *
	 * @var array
	 * @access public
	 * @since 1.0
	 */
	public $ColumnHeader;

	/**
	 * Constructor for ObjtrackerDashboard in support of Dashboard report.
	 *
	 * @since    1.0
	 * @params   $bsdriver           BsDriver object
	 * @params   $bsuser             BsUser object
	 * @params   $dbProcList         Name of stored procedure for listing page items
	 * @params   $dbProcParms        Array of parameters to stored procedure
	 * @returns  void
	 */
	public function __construct( $bsdriver, $bsuser, $dbProcList, $dbProcArgs, $dbProcParms )
	{
		$this->bsdriver = $bsdriver;
		$this->bsuser   = $bsuser;

	//	$bsdriver->trace_text( ' ObjtrackerDashboard' );
		$this->DbResults = $bsdriver->platform_db_query( $dbProcList, $dbProcArgs, $dbProcParms );
	//	$bsdriver->trace_text( ' rows=' . count( $this->DbResults ) );

		$this->ColumnHeader = array( 'ID', 'Status', 'Category', 'Dept', 'Title', 'Target', 'Measurement', 'PopupText' );
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

	//	$bsdriver->trace_text( ' gather_data' );
		$this->Objectives = array();

		$o          = null;
		$sLastOID   = '';
		$skipToNext = false;
		$gather     = false;

		// All periods represented for each objective.
		// Capture last with value or if none, the null row.

		foreach ( $this->DbResults as $row ) {
			$C_OID = $row[$bsdriver->Field->C_OID];
			$C_MID = $row[$bsdriver->Field->C_MID];
			$C_PeriodStarting = $row[$bsdriver->Field->C_PeriodStarting];

			if ( $sLastOID != $C_OID ) {
				// on new object, grab it

				$o = new ObjtrackerMeasuredObjective();
				array_push( $this->Objectives, $o );
				//
				$gather     = true;
				$skipToNext = $C_MID == '' ? true : false;
			} elseif ( !$skipToNext && $C_MID == '' ) {
				// existing object has null measurement

				$skipToNext = true;
				$gather     = false;
			} else {
				// existing object has measurement

				$gather = true;
			}
			if ( $gather ) {
				// Set or overlay object with same OID key

				$o->sOID            = $C_OID;
				$o->sMID            = $C_MID;
				$o->sType           = $row[$bsdriver->Field->C_Type];
				$o->sTitle          = $row[$bsdriver->Field->C_Title];
				$o->sStatus         = $row[$bsdriver->Field->C_Status];
				$o->cFrequencyID    = $row[$bsdriver->Field->C_FrequencyID];
				$o->cMetricTypeID   = $row[$bsdriver->Field->C_MetricTypeID];
				$o->sDeptTitle2     = $row[$bsdriver->Field->C_DeptTitle2];
				$o->sTarget         = $row[$bsdriver->Field->C_Target];
				$o->sMeasurement    = $row[$bsdriver->Field->C_Measurement];
				$o->dPeriodStarting = $C_PeriodStarting;

				if ( $o->sMeasurement == null )
					$o->sMeasurement = ' ';
				if ( $o->sMeasurement == 'Missing' )
						$o->sMeasurement = ' ';
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

//		$bsdriver->trace_text( ' setup_dataset' );
		$this->DataTable = array();

		// Fill data rows with data
		$lastOID     = '';
		$lastDataRow = null;

		foreach ( $this->Objectives as $measure ) {
			if ( $lastOID == '' || $lastOID != $measure->sOID ) {
				$row = array();
				$row[ObjtrackerDashboard::_C_ID]          = $measure->sOID;
				$row[ObjtrackerDashboard::_C_Status]      = $measure->sStatus;
				$row[ObjtrackerDashboard::_C_Category]    = $measure->sType;
				$row[ObjtrackerDashboard::_C_Dept]        = $measure->sDeptTitle2;
				$row[ObjtrackerDashboard::_C_Title]       = $measure->sTitle;
				$row[ObjtrackerDashboard::_C_Target]      = $measure->sTarget;
				$row[ObjtrackerDashboard::_C_Measurement] = $measure->sMeasurement;
				$row[ObjtrackerDashboard::_C_PopUpText]   = ObjtrackerEasyStatic::hovertext_by_status( $measure->sStatus );

				$target = new ObjtrackerMetricValue( $measure->cMetricTypeID, $measure->sTarget );
				$row[ObjtrackerDashboard::_C_Sort_Target] = $target->value();

				$measurement = new ObjtrackerMetricValue( $measure->cMetricTypeID, $measure->sMeasurement );
				$row[ObjtrackerDashboard::_C_Sort_Measurement] = $measurement->value();

				array_push( $this->DataTable, $row );
				$lastDataRow = $row;
			} elseif ( $measure->sMeasurement != null && $measure->sMeasurement != '' ) {
				$lastDataRow[ObjtrackerDashboard::_C_Measurement] = $measure->sMeasurement;
			}
			$lastOID = $measure->sOID;
		}
	}
}
?>
