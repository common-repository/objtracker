<?php
/**
 * Class used by Baseline.php to report on targets verus measured results over multiple years.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * ObjtrackerBaseline class supports Baseline.php.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerBaseline
{
	const _C_ID             = 0;
	const _C_Status         = 1;
	const _C_PopUpText      = 2;
	const _C_Dept           = 3;
	const _C_Title          = 4;
	const _C_sFyTarget      = 5;
	const _C_Sort_sFyTarget = 6;
	const _C_Measurement    = 7;

	/**
	 * Holds the environment object.
	 *
	 * @var BsDriver
	 * @access private
	 * @since 1.0
	 */
	private $BsDriver;

	/**
	 * Holds the user object.
	 *
	 * @var BsUser
	 * @access private
	 * @since 1.0
	 */
	private $BsUser;

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
	 * Holds first fiscal year ID to display.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $FirstFiscalYear1;

	/**
	 * Holds array of fiscal year ids for dynamic column headers on the right of the grid view.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $FiscalYearIDs;

	/**
	 * Holds array of fiscal year title for dynamic column headers on the right of the grid view.
	 *
	 * @var array
	 * @access public
	 * @since 1.0
	 */
	public $FiscalYears;

	/**
	 * Holds array of ObjtrackerGvColumn used as column headers in the grid view.
	 *
	 * @var array of ObjtrackerGvColumn
	 * @access public
	 * @since 1.0
	 */
	public $GvColumns;

	/**
	 * Holds array of strings used as column headers in the grid view.
	 *
	 * @var array
	 * @access public
	 * @since 1.0
	 */
	public $ColumnHeader;

	/**
	 * Constructor for ObjtrackerBaseline in support of Baseline report.
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

		//$bsdriver->trace_text( ' ObjtrackerBaseline' );

		$this->GvColumns    = array();
		$this->ColumnHeader = array( 'ID', 'Status', 'PopupText', 'Dept', 'Title', 'Target' );
		$this->get_db_fiscalyears();

		$this->DbResults = $bsdriver->platform_db_query( $dbProcList, $dbProcArgs, $dbProcParms );
		//$bsdriver->trace_text( ' rows=' . count( $this->DbResults ) );
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

		$this->Objectives = array();

		$o          = null;
		$sLastOID   = '';
		$skipToNext = false;
		$gather     = false;

		// All periods represented for each objective.
		// Capture last with value or if none, the null row.
		foreach ( $this->DbResults as $row ) {
			$C_OID            = $row[$bsdriver->Field->C_OID];
			$C_MID            = $row[$bsdriver->Field->C_MID];
			$C_PeriodStarting = $row[$bsdriver->Field->C_PeriodStarting];

			if ( $sLastOID != $C_OID ) {
				// on new object, grab it

				$o = new ObjtrackerMeasuredObjective();
				array_push( $this->Objectives, $o );

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

				$o->sOID    = $C_OID;
				$o->sMID    = $C_MID;
				$o->sTitle  = $row[$bsdriver->Field->C_Title];
				$o->sStatus = $row[$bsdriver->Field->C_Status];

				$o->cFrequencyID  = $row[$bsdriver->Field->C_FrequencyID];
				$o->cMetricTypeID = $row[$bsdriver->Field->C_MetricTypeID];
				$o->sDeptTitle2   = $row[$bsdriver->Field->C_DeptTitle2];
				$o->sTarget       = $row[$bsdriver->Field->C_mttTarget];
				$o->sFyTarget     = $row[$bsdriver->Field->C_mttTarget];
				$o->sMeasurement  = $row[$bsdriver->Field->C_Measurement];

				if ( $o->sMeasurement == 'Missing' ) {
					$o->sMeasurement = '';
				}
				$o->dPeriodStarting = $C_PeriodStarting;
			}
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
		$bsuser   = $this->bsuser;

		$this->DataTable = array();

		// Fill data rows with data
		$lastOID     = '';
		$lastDataRow = null;
		$row         = null;

		foreach ( $this->Objectives as $measure ) {
			if ( $lastOID != $measure->sOID ) {
				if ( isset( $row ) ) {
					array_push( $this->DataTable, $row );
				}
				$row = array();
				for ( $i = 0; $i <= ObjtrackerBaseline::_C_Measurement; $i++ ) {
					$row[$i] = 'x';
				}
				for ( $iFy = 0; $iFy < 2 * count( $this->FiscalYears ); $iFy += 2 ) {
					$row[$iFy + ObjtrackerBaseline::_C_Measurement]     = '-';
					$row[$iFy + ObjtrackerBaseline::_C_Measurement + 1] = 0;
				}
			}
			$row[ObjtrackerBaseline::_C_ID]        = $measure->sOID;
			$row[ObjtrackerBaseline::_C_Status]    = $measure->sStatus;
			$row[ObjtrackerBaseline::_C_PopUpText] = ObjtrackerEasyStatic::hovertext_by_status( $measure->sStatus );
			$row[ObjtrackerBaseline::_C_Dept]      = $measure->sDeptTitle2;
			$row[ObjtrackerBaseline::_C_Title]     = $measure->sTitle;
			$row[ObjtrackerBaseline::_C_sFyTarget] = $measure->sFyTarget;

			$target = new ObjtrackerMetricValue( $measure->cMetricTypeID, $measure->sTarget );
			$row[ObjtrackerBaseline::_C_Sort_sFyTarget] = $target->value();

		//	$bsdriver->trace_text( '<br /> row(' . $row[ObjtrackerBaseline::_C_ID] . ' m' . $measure->sMeasurement . ')' );

			for ( $iFy = 0; $iFy < 2 * count( $this->FiscalYears ); $iFy += 2 ) {
				if ( $measure->sMeasurement != null && $measure->sMeasurement != '' ) {
					$myFy = ObjtrackerEasyStatic::first_fy_year_bydate( $bsuser->FirstMonth, $measure->dPeriodStarting ) - $this->FirstFiscalYear1;
		//			$bsdriver->trace_text( ' iFy=' . $iFy . ' myFy=' . $myFy . '=' . $measure->dPeriodStarting . ')' );
					if ( $iFy == 2 * $myFy ) {
						$bsdriver->trace_text( ' i=my(' . $measure->sMeasurement . ')' );
						$row[$iFy + ObjtrackerBaseline::_C_Measurement] = $measure->sMeasurement;

						$measurement = new ObjtrackerMetricValue( $measure->cMetricTypeID, $measure->sMeasurement );
						$row[$iFy + ObjtrackerBaseline::_C_Measurement + 1] = $measurement->value();
					}
				}
			}
			$lastOID = $measure->sOID;
		}
		if ( isset( $row ) ) {
			array_push( $this->DataTable, $row );
		}
	}

	/**
	 * Get array of fiscal years defined and add column headers for current ones.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function get_db_fiscalyears()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->FirstFiscalYear1 = '';
		$this->FiscalYearIDs    = array();
		$this->FiscalYears      = array();

		$newColumnID = ObjtrackerBaseline::_C_Measurement;
		$results     = $bsdriver->platform_db_query( 'P_FiscalYearList', '( %d, %d )', array( $bsuser->OrgID, $bsuser->ID ) );

		foreach ( $results as $row ) {
			if ( $this->FirstFiscalYear1 == '' ) {
				$this->FirstFiscalYear1 = $row[$bsdriver->Field->C_ID];
		//		$bsdriver->trace_text( ' thisFY=' . $this->FirstFiscalYear1 );
			}
			if ( $row[$bsdriver->Field->C_ID] > $bsuser->FiscalYear1 )
				break;
			array_push( $this->FiscalYearIDs, $row[$bsdriver->Field->C_ID] );
			array_push( $this->FiscalYears, $row[$bsdriver->Field->C_Title] );
			array_push( $this->ColumnHeader, $row[$bsdriver->Field->C_Title] );

			array_push( $this->GvColumns, new ObjtrackerGvColumn( $row[$bsdriver->Field->C_Title], $newColumnID + 1 ) );
			array_push( $this->GvColumns , new ObjtrackerGvColumn( 'Dummy' . $newColumnID, $newColumnID + 1 ) );
			$newColumnID += 2;
		}
	}
}
?>
