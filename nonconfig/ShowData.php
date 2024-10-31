<?php
/**
 * Formats tab-separted output for downloads.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */
/*

*/

/**
 * Returns this page unique html text.
 *
 * @since    1.0
 * @param    object  $bsdriver   The environment object
 * @param    object  $bsuser     The user object
 * @return   string              Page's unique html text.
 */
function bs_showdata( $bsdriver, $bsuser )
{
	if ( !isset( $_GET['fname'] ) )
		die;

	if ( isset( $_GET['mimetype'] ) ) {
		// For Drupal, write the httpheaders
		$bsdriver->write_httpheaders();

		clear_buffers2();
		$extract = new ObjtrackerExtract( $bsdriver, $bsuser );
		$extract->download();

		die();
	} else {
		$extract = new ObjtrackerExtract( $bsdriver, $bsuser );
		return $extract->extract_into_gridview();
	}
}

/**
 * Clears platform buffers.
 *
 * @since    1.0
 * @return   void
 */
function clear_buffers2()
	{
		# installing at the toplevel
		$my_default_level = ob_get_level();		// learn about already set output buffers
		$my_has_buffer    = ob_start(); 		// my output buffer, with flagging

		# burning down (somewhere after)
		if ( $my_has_buffer ) {
			$c = ob_get_level() - $my_default_level;
			if ( $c > 0 ) { 
				while ( $c-- ) {
					ob_end_clean();
				}
			}
		}

		flush();
	}

/**
 * Tab-separted data extraction processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerExtract
{
	/**
	 * Holds the stored procedure name.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $DbProcList;

	/**
	 * Holds array of parameters for stored procedure.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $DbProcParms;

	/**
	 * Holds derived name of extract file.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $FileName;

	/**
	 * True/false indicating if URL parameters have matching code.
	 *
	 * @var bool
	 * @access private
	 * @since 1.0
	 */
	private $Match;

	/**
	 * Holds database parms as a string for display.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $Parms;

	/**
	 * Holds results of database query.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $DbResults;

	/**
	 * True/false indicating of some extracts, the unfiltered query response.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $Raw;

	/**
	 * Constructor for ObjtrackerExtract
	 *
	 * @since    1.0
	 * @param    object  $bsdriver   The environment object
	 * @param    object  $bsuser     The user object
	 * @return   string              Page's unique html text.
	 */
	function __construct( $bsdriver, $bsuser )
	{
		$this->BsDriver = $bsdriver;
		$this->BsUser   = $bsuser;

		$this->FileName = $_GET['fname'];

		$this->DbProcList  = substr( $this->FileName, 0, strlen( $this->FileName ) - 4 );
		$this->DbProcParms = array( $bsuser->OrgID, $bsuser->ID );

		if ( isset( $_GET['A'] ) ) {
			$this->DbProcParms[2] = $_GET['V'];
			if ( isset( $_GET['A2'] ) ) {
				$this->DbProcParms[3] = $_GET['V2'];
				if ( isset( $_GET['A3'] ) ) {
					$this->DbProcParms[4] = $_GET['V3'];
				}
			}
			$this->Parms = Implode( $this->DbProcParms, ' ' );
		} else {
			$this->Parms = 'none';
		}
		$this->Raw = true;
		switch ( $this->DbProcList ) {
		case 'Admin-Departments':
			$this->DbProcList = 'DepartmentList';
			$this->DbProcArgs = '( %d, %d, %s, %s, %s )';
			$this->Match      = true;
			break;
		case 'Admin-FiscalYears':
			$this->DbProcList = 'FiscalYearList';
			$this->DbProcArgs = '( %d, %d )';
			$this->Match      = true;
			break;
		case 'Admin-Frequency':
			$this->DbProcList = 'FrequencyList';
			$this->DbProcArgs = '( %d, %d )';
			$this->Match      = true;
			break;
		case 'Admin-MetricTypes':
			$this->DbProcList = 'MetricTypeList';
			$this->DbProcArgs = '( %d, %d )';
			$this->Match      = true;
			break;
		case 'Admin-ObjectiveTypes':
			$this->DbProcList = 'ObjectiveTypeList';
			$this->DbProcArgs = '( %d, %d )';
			$this->Match      = true;
			break;
		case 'Admin-People':
			$this->DbProcList = 'PersonList';
			$this->DbProcArgs = '( %d, %d, %s )';
			$this->Match      = true;
			break;
		case 'Objectives':
			$this->DbProcList = 'ObjectiveList';
			$this->DbProcArgs = '( %d, %d, %s, %s )';
			$this->Match      = true;
			break;
		case 'Dashboard':
			$this->DbProcList = 'Dashboard';
			$this->DbProcArgs = '( %d, %d, %d )';
			$this->Match      = true;

			if ( !isset( $_GET['Raw'] ) ) {
				$this->Raw = false;

				include OBJTRACKER_NON2INC_DIR . 'class-dashboard' . OBJTRACKER_CONTENT_MTYPE;
				$this->Object = new ObjtrackerDashboard( $bsdriver, $bsuser, 'P_' . $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );

				$this->Object->gather_data();
				$this->Object->setup_dataset();
				$this->DbResults    = $this->Object->DataTable;
				$this->ColumnHeader = $this->Object->ColumnHeader;
			}
			break;
		case 'Baseline':
			$this->DbProcList = 'Baseline';
			$this->DbProcArgs = '( %d, %d )';
			$this->Match = true;

			if ( !isset( $_GET['Raw'] ) ) {
				$this->Raw = false;

				include OBJTRACKER_NON2INC_DIR . 'class-baseline' . OBJTRACKER_CONTENT_MTYPE;
				$this->Object = new ObjtrackerBaseline( $bsdriver, $bsuser, 'P_' . $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );

				$this->Object->gather_data();
				$this->Object->setup_dataset();
				$this->DbResults    = $this->Object->DataTable;
				$this->ColumnHeader = $this->Object->ColumnHeader;
			}
			break;
		default:
			$this->Match = false;
			break;
		}
		if ( $this->Match ) {
			if ( $this->Raw ) {
	//			$bsdriver->trace_text( ' <br>RawQuery' );
				$this->DbResults = $bsdriver->platform_db_query( 'P_' . $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );

	//			$bsdriver->trace_text( ' ColumnHeaders' );
				$this->ColumnHeader = str_replace( 'C_', '', $bsdriver->DbColumns );
			} elseif ( $this->DbProcList == 'Baseline' ) {
	//			$bsdriver->trace_text( ' <br>unRawBaseline' );
			} elseif ( $this->DbProcList == 'Dashboard' ) {
	//			$bsdriver->trace_text( ' <br>RawDashboard' );
			} else
				die;
		}
	}
	
	/**
	 * Output tab-separated data for excel-like products
	 *
	 * Note that CodeSniffer/Coding standards wants a escape function
	 * for the echoed output. The "echo '' . " defeats that! Yes, I'm guilty.
	 *
	 * @since    1.0
	 * @return   void
	 */
	function download()
	{
		if ( $this->Match ) {
			echo '' . implode( $this->ColumnHeader, "\t" ) . "\n";
			print "\n";
			foreach ( $this->DbResults as $row ) {
				echo '' . implode( "\t", $row ) . "\n";
			}
		} else {
			echo '' . '\n\n' . __( 'Unmatched' ) . "\n%s\t%s\n", $this->DbProcList , $this->Parms;
		}
	}
	/**
	 * For debugging, extract the data as a gridview
	 *
	 * @since    1.0
	 * @return   string                Fragement of html containing a gridview
	 */
	function extract_into_gridview()
	{
		$bsdriver = $this->BsDriver;

		if ( $this->Match ) {
			$columnHeader = '';
			foreach ( $this->ColumnHeader as $column ) {
				$columnHeader .= '<th>' . $column . '</th>';
			}
			$bsdriver->trace_text( ' <br>Data' );
			$txt = '';
			foreach ( $this->DbResults as $row ) {
				$txt .= "<tr>\n";
				foreach ( $row as $column ) {
					$txt .= '<td>' . $column . '</td>';
				}
				$txt .= "</tr>\n";
			}
			return ObjtrackerEasyStatic::table_start( '' )
					. ObjtrackerEasyStatic::table_tr_start( '' )
					. $columnHeader
					. ObjtrackerEasyStatic::table_tr_end()
					. $txt
					. ObjtrackerEasyStatic::table_end();
		} else {
			return ObjtrackerEasyStatic::table_start( "style='width:300px;'" )
					. ObjtrackerEasyStatic::table_tr_start( '' )
					. ObjtrackerEasyStatic::table_td( '', __( 'Unmatched' ) . $this->DbProcList )
					. ObjtrackerEasyStatic::table_td( '', $this->Parms )
					. ObjtrackerEasyStatic::table_tr_end()
					. ObjtrackerEasyStatic::table_end();
		}
	}
}

?>
