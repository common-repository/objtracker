<?php
/**
 * Reports on objectives without user controls.
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
function bs_reportobjectives( $bsdriver, $bsuser )
{
	$report = new BsReport(
		$bsdriver,
		$bsuser,
		'P_ReportMeasurements',
		'( %d, %d, %s, %s )',
		array( $bsuser->OrgID, $bsuser->ID, $_GET['Dept'], $_GET['Time'], )
		);
	return $report->Response();
}

/**
 * Report processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsReport extends ObjtrackerPage
{
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
	 * Holds the database query results.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $DbResults;

	/**
	 * Holds the stored procedure name.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $DbProcList;

	/**
	 * Sprintf string for arguments of stored procedure
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $DbProcArgs;

	/**
	 * Holds the parameters of the stored procedure.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $DbProcParms;

	/**
	 * Holds the url value for current or all objectives.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $Time;

	/**
	 * Holds the displayed value of the URL input for user/department/
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $Department;

	const PARM_DEPT = 2;
	const PARM_TIME = 3;

	/**
	 * Constructor for BsReport.
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
		$this->bsdriver    = $bsdriver;
		$this->bsuser      = $bsuser;
		$this->DbProcList  = $dbProcList;
		$this->DbProcArgs  = $dbProcArgs;
		$this->DbProcParms = $dbProcParms;
	}

	/**
	 * Create report header
	 *
	 * @since    1.0
	 * @return   void
	 */
	public function report_header()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		// Show person/department/all
		if ( substr( $this->DbProcParms[self::PARM_DEPT], 0, 1 ) == 'U' ) {
			$this->Department = $bsuser->UserName;
		} else {
			$i = strpos( $this->DbProcParms[self::PARM_DEPT], '-' );
			if ( $i === false ) {
				$results = $bsdriver->platform_db_query(
					'P_DepartmentList',
					'( %d, %d, %s, %s, %s )',
					array( $bsuser->OrgID, $bsuser->ID, 'False', 'False', 'NotUsed' )
					);
				foreach ( $results as $row ) {
					if ( $row[$bsdriver->Field->C_ID] == $this->DbProcParms[self::PARM_DEPT] ) {
						$this->Department = $row[$bsdriver->Field->C_Title];
					}
				}
			} else {
				$this->Department = __( 'For all departments' );
			}
		}

		// Show time used
		$this->Time = $this->DbProcParms[self::PARM_TIME] == 'Current'
				? __( 'Current fiscal year' )
				: __( 'All fiscal years' );
		return ;
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

		$this->report_header();

		$dbResults = $bsdriver->platform_db_query( $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );

		$txt         = '';
		$sLastOID    = '';
		$sLastFy     = '';
		$iObjectives = 0;
		foreach ( $dbResults as $row ) {
			// New object
			if ( $row[$bsdriver->Field->C_OID] != $sLastOID ) {
				$bsdriver->trace_text( ' obj=' . $row[$bsdriver->Field->C_OID] );
				if ( $sLastOID != '' )
					$txt .= ObjtrackerEasyStatic::table_tr_end() . ObjtrackerEasyStatic::table_end();

				$iObjectives++;

				// Objective's descriptive info
				$sLastFy     = '';
				$description = $row[$bsdriver->Field->C_Description];

				$txt .= "<br /><br />\n";
				$txt .= "<span style='font-weight:bold;'>" . $row[$bsdriver->Field->C_Title] . "</span>\n";

				if ( $description != null && strlen( $description ) > 0 ) {
					$sa = preg_split( "/\n/", $description );
					if ( count( $sa ) > 1 ) {
						$description = Implode( $sa, '<br/>' );
					}
				}
				$txt .= "<table class='BssReportTable'>\n";

				$txt .= ObjtrackerEasyStatic::table_tr_start( "class='BssReportTr'" );
				$txt .= ObjtrackerEasyStatic::table_td( "class='BssReportTd1'", '&nbsp;' )
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd4' colspan='3'", $description )
					. ObjtrackerEasyStatic::table_tr_end();
				$txt .= ObjtrackerEasyStatic::table_tr_start( "class='BssReportTr'" );
				$txt .= ObjtrackerEasyStatic::table_td( "class='BssReportTd4' colspan='4'", '&nbsp;' )
					. ObjtrackerEasyStatic::table_tr_end();

				$txt .= ObjtrackerEasyStatic::table_tr_start( "class='BssReportTr'" );
				$txt .= ObjtrackerEasyStatic::table_td( "class='BssReportTd1'", '&nbsp;' )
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd2'", __( 'Fiscal Years:' ) )
					. ObjtrackerEasyStatic::table_td(
						"class='BssReportTd4' colspan='2'",
						$row[$bsdriver->Field->C_FY1Title] . ' ' . __( 'to' ) . ' ' . $row[$bsdriver->Field->C_FY2Title]
						)
					. ObjtrackerEasyStatic::table_tr_end();

				$txt .= ObjtrackerEasyStatic::table_tr_start( "class='BssReportTr'" );
				$txt .= ObjtrackerEasyStatic::table_td( "class='BssReportTd1'", '&nbsp;' )
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd2'", __( 'Type:' ) )
					. ObjtrackerEasyStatic::table_td(
						"class='BssReportTd4' colspan='2'",
						$row[$bsdriver->Field->C_IsPublic]
							. '-' . $row[$bsdriver->Field->C_Type]
							. '-' . $row[$bsdriver->Field->C_Frequency]
						)
					. ObjtrackerEasyStatic::table_tr_end();

				$txt .= ObjtrackerEasyStatic::table_tr_start( "class='BssReportTr'" );
				$txt .= ObjtrackerEasyStatic::table_td( "class='BssReportTd1'", '&nbsp;' )
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd2'", __( 'Source:' ) )
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd4' colspan='2'", $row[$bsdriver->Field->C_Source] )
					. ObjtrackerEasyStatic::table_tr_end();

				$txt .= ObjtrackerEasyStatic::table_tr_start( "class='BssReportTr'" );
				$txt .= ObjtrackerEasyStatic::table_td( "class='BssReportTd1'", '&nbsp;' )
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd2'", __( 'Owner:' ) )
					. ObjtrackerEasyStatic::table_td(
						"class='BssReportTd4' colspan='2'", 
						$row[$bsdriver->Field->C_FullName] . ' ' . __( 'of' ) . ' ' . $row[$bsdriver->Field->C_DeptTitle2]
						)
					. ObjtrackerEasyStatic::table_tr_end();
			}
			// Targets
			if ( $row[$bsdriver->Field->C_FiscalYear] != $sLastFy ) {
				$txt .= ObjtrackerEasyStatic::table_tr_start( "class='BssReportTr'" );
				$txt .= ObjtrackerEasyStatic::table_td( "class='BssReportTd1'", '&nbsp;' )
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd2'", __( 'FY' ) . ' ' . $row[$bsdriver->Field->C_FiscalYear] )
					. ObjtrackerEasyStatic::table_td(
						"class='BssReportTd' colspan='2'",
						__( 'Target:' ) . ' ' . $row[$bsdriver->Field->C_Target]
							. '&nbsp;&nbsp;' . __( 'Close(Green): ' ) . $row[$bsdriver->Field->C_Target1]
							. '&nbsp;&nbsp;' . __( 'Far(Yellow): ' ) . $row[$bsdriver->Field->C_Target2]
						)
					. ObjtrackerEasyStatic::table_tr_end();

				$sLastFy = $row[$bsdriver->Field->C_FiscalYear];
			}
			$dtStarting   = substr( $row[$bsdriver->Field->C_PeriodStarting], 0, 10 );
			$sMeasurement = $row[$bsdriver->Field->C_Measurement];
			if ( $sMeasurement == __( 'Missing' ) )
				$sMeasurement = '';

			if ( $row[$bsdriver->Field->C_MID] != '' ) {
				$sNotes = $row[$bsdriver->Field->C_Notes];

				$txt .= ObjtrackerEasyStatic::table_td( "class='BssReportTd1'", '&nbsp;' )
					. ObjtrackerEasyStatic::table_td(
						"class='BssReportTd2'", 
						'&nbsp;&nbsp;&nbsp;' . $dtStarting
						. " <img alt='" . $row[$bsdriver->Field->C_Status] . "' src='" . $bsdriver->PathImages
						. ObjtrackerEasyStatic::get_statusurl( $row[$bsdriver->Field->C_Status] ) . "'"
						)
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd3'", '&nbsp;&nbsp;&nbsp;' . $sMeasurement )
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd4'", $sNotes )
					. ObjtrackerEasyStatic::table_tr_end();
			} else {
				$txt .= ObjtrackerEasyStatic::table_td( "class='BssReportTd1'", '&nbsp;' )
						. ObjtrackerEasyStatic::table_td(
							"class='BssReportTd2'",
							'&nbsp;&nbsp;&nbsp;' . $dtStarting
							. " <img alt='LATE' src='" . $bsdriver->PathImages . ObjtrackerEasyStatic::get_statusurl( 'LATE' ) . "'>"
							)
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd3'", '&nbsp;&nbsp;&nbsp;' . $sMeasurement )
					. ObjtrackerEasyStatic::table_td( "class='BssReportTd4'", '&nbsp;' )
					. ObjtrackerEasyStatic::table_tr_end();
			}
			$sLastOID = $row[$bsdriver->Field->C_OID];
		}
		if ( $sLastOID != '' )
			$txt .= ObjtrackerEasyStatic::table_end();

		return '<h1>' . __( 'Objectives Report' ) . '</h1>'
				. $this->Time
				. '&nbsp;&nbsp;&nbsp;'
				. $this->Department
				. '&nbsp;&nbsp;&nbsp;'
				. __( 'Objectives returned:' ) . ' ' . $iObjectives . "<br />\n"
				. $txt;
	}
}

?>
