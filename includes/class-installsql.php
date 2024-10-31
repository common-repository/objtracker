<?php
/**
 * Creates the database environment.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Install class must be subclassed.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerInstallSql
{
	/**
	 * Test trace.
	 *
	 * @var Trace
	 * @access private
	 * @since 1.0.3
	 */
	private $Trace;

	/**
	 * Holds the environment object.
	 *
	 * @var BsDriver
	 * @access private
	 * @since 1.0
	 */
	private $BsDriver;

	/**
	 * Initial file name to process or 'all' for all of them!
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $fname;

	/**
	 * Prefix for database tables, stored procedures, functions, and triggers.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	private $prefix;	

	/**
	 * Prefix for database tables, stored procedures, functions, and triggers.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 */
	public $Messages;	

	/**
	 * ObjtrackerInstallSql constructor.
	 *
	 * @since    1.0
	 * @param    string              Path of the sql directory.
	 * @param    string              File name/directory name.
	 * @param    string              Driver object.
	 * @param    string              Prefix for all tables, procs, ....
	 * @return   int                 Return code.
	 */
	function __construct( $directory, $fname, $bsdriver, $prefix )
	{
		$this->directory = $directory;
		$this->fname     = $fname;
		$this->BsDriver  = $bsdriver;
		$this->prefix    = $prefix;
	}

	/**
	 * Retrive messages.
	 *
	 * @since    1.0.3
	 * @return   string                Return code.
	 */
	public function get_messages()
	{
		return implode( '<br />', $this->Messages );
	}

	/**
	 * Displays message.
	 *
	 * @since    1.0
	 * @param    string              Text to write.
	 * @return   void                Return code.
	 */
	private function save_message( $string )
	{
		array_push( $this->Messages, $string );
	}

	/**
	 * Updates database with contents from one directory.
	 *
	 * @since    1.0
	 * @return   int                 Count of errors
	 */
	function install()
	{
		$this->Trace    = false;
		$this->Messages = array();
		$bsdriver       = $this->BsDriver;

		$rv = 0;
		if ( !isset( $this->fname ) || $this->fname == '' ) {
			$this->save_message( 'ERROR no fname=' . $this->fname . "\n" );
			return 1;
		}

		$pos = strpos( $this->fname, '.sql' );
		if ( $this->fname == 'all' ) {
			$rv += $this->output_one_file( 'Tables/Schema.sql' );
			$rv += $this->output_one_directory( 'Functions' );
			$rv += $this->output_one_file( 'Tables/AuditProcs.sql' );
			$rv += $this->output_one_directory( 'Triggers' );
			$rv += $this->output_one_directory( 'Procs' );
			$rv += $this->output_one_file( 'Tables/Org0Init.sql' );
			$rv += $this->output_one_file( 'Tables/InstallState.sql' );
		} elseif ( $this->fname == 'install:0' ) {
			$rv += $this->output_one_directory( 'Drops' );
		} elseif ( $this->fname == 'install:1' ) {
			$rv += $this->output_one_file( 'Tables/Schema.sql' );
		} elseif ( $this->fname == 'install:2' ) {
			$rv += $this->output_one_directory( 'Functions' );
		} elseif ( $this->fname == 'install:3' ) {
			$rv += $this->output_one_file( 'Tables/AuditProcs.sql' );
			$rv += $this->output_one_directory( 'Procs' );
		} elseif ( $this->fname == 'install:4' ) {
			$rv += $this->output_one_file( 'Tables/Org0Init.sql' );
			$rv += $this->output_one_file( 'Tables/InstallState.sql' );
		// } elseif ( $this->fname == 'install:5' ) {
		} elseif ( $this->fname == 'install:6' ) {
			$rv += $this->output_one_directory( 'Triggers' );
		} elseif ( $this->fname == 'fix:0001' ) {
			$rv += $this->output_one_file( 'Functions/StatusCompare.sql' );
		} elseif ( $this->fname == 'fix:0002' ) {
			$rv += $this->output_one_file( 'Functions/FormatDate.sql' );
			$rv += $this->output_one_file( 'Functions/FormatSortedDate.sql' );
			$rv += $this->output_one_file( 'Procs/MeasurementList.sql' );
		} elseif ( $this->fname == 'fix:0003' ) {
			$rv += $this->output_one_file( 'Procs/DepartmentList.sql' );
			$rv += $this->output_one_file( 'Procs/Alerts.sql' );
			$rv += $this->output_one_file( 'Procs/ObjectiveList.sql' );
			$rv += $this->output_one_file( 'Procs/MetricTypeList.sql' );
		} elseif ( $pos !== false ) {
			//$this->save_message(  ' single file' . $this->fname );
			$rv += $this->output_one_file( $this->fname );
		} else {
			$rv += $this->output_one_directory( $this->fname );
		}

		if ( $rv > 0 ) {
			$this->save_message( $rv . ' ' . __( 'Failure(s) found.' ) );
		}
		return $rv;
	}

	/**
	 * Updates database with contents from one directory.
	 *
	 * @since    1.0
	 * @param    string              Directory name.
	 * @return   int                 Return code.
	 */
	function output_one_directory( $fname )
	{
		$rv = 0;
		if ( $this->Trace ) {
			$this->save_message( ' ' . $fname );
		}
	
		if ( $handle = opendir( $this->directory . $fname ) ) {
			while ( false !== ( $entry = readdir( $handle ) ) ) {
				$pos = strpos( $entry, '.sql' );
				if ( substr( $entry, 0, 1 ) != '.' && $pos != false ) {
					$twochar = substr( $entry, 0, 2 );
					if ( $twochar != 'F_' && $twochar != 'P_' && $twochar != 'T_' && $twochar != 'A_' ) {
						$rv += $this->output_one_file( $fname . '/' . $entry );
					}
				}
			}
			closedir( $handle );
		}
		return $rv;
	}

	/**
	 * Updates database with contents from one file.
	 *
	 * @since    1.0
	 * @param    string              File name.
	 * @return   int                 Return code.
	 */
	function output_one_file( $fname )
	{
		if ( $this->Trace ) {
			$this->save_message( '  ' . $fname );
		}

		$rv      = 0;
		$array   = preg_split( "/\n/", file_get_contents( $this->directory . $fname ) );
		$i       = 1;
		$inDelim = false;
		$cmd     = array();
	
		foreach ( $array as $line ) {
			//$line .= " \n";
			$mugged = $this->mug_line( rtrim( $line ) );
	
			if ( strlen( $mugged ) >= 5 && substr( $mugged, 0, 5 ) == 'DELIM' ) {
			//	print "DELIM\n";
				$inDelim = true;
				$cmd     = array();
			} elseif ( strlen( $mugged ) >= 2 && substr( $mugged, 0, 2 ) == '$$' ) {
			//	print "$$\n";
				$inDelim = false;
				$rv     += $this->call_mysql( $fname, $cmd );
				$cmd     = array();
			} elseif ( $inDelim ) {
				//print "\nin=" . $mugged ."\n";
				array_push( $cmd, $mugged );
			} elseif ( strpos( $mugged, ';' ) === false ) {
				//$this->save_message( "\nno;=" . $mugged );
				array_push( $cmd, $mugged );
			} else {
				//$this->save_message(  "\nyes;=" . $mugged );
				array_push( $cmd, $mugged );
				$rv += $this->call_mysql( $fname, $cmd );
				$cmd = array();
			}
//			if ( $rv > 0 ) {
//				return $rv;
//			}
		}
		return $rv;
	}
	
	/**
	 * Inserts prefix into text.
	 *
	 * @since    1.0
	 * @param    string              Text line.
	 * @param    string              Prefix for all tables, procs, ....
	 * @return   int                 Return code.
	 */
	function mug_line( $line )
	{
		if ( strlen( $line ) > 1 && substr( $line, 0, 2 ) == '--' )
			return '';
	
		if ( strlen( $line ) > 5 && substr( $line, 0, 6 ) == 'GRANT ' )
			return '';
	
//		if ( $this->prefix != "''" && strlen( $line ) > 5 ) {
//			$line = str_replace( 'T_', $this->prefix . 'T_', $line );
//			$line = str_replace( 'F_', $this->prefix . 'F_', $line );
//			$line = str_replace( 'P_', $this->prefix . 'P_', $line );
//			$line = str_replace( 'A_', $this->prefix . 'A_', $line );
//			$line = str_replace( 'X_', $this->prefix . 'X_', $line );
//		}
	
		return ( $line );
	}

	/**
	 * Updates database with contents from input array.
	 *
	 * @since    1.0
	 * @param    string              File name.
	 * @param    string              Prefix for all tables, procs, ....
	 * @return   int                 Return code.
	 */
	function call_mysql( $fname, $sqlArray )
	{
		$rv       = 0;
		$bsdriver = $this->BsDriver;

		$sqlText = implode( $sqlArray, "\n" );

		$results = '';
		try {
			$results = $bsdriver->platform_db_install( $sqlText );
		} 
		catch ( Exception $e ) {
			$this->save_message(  _( 'Exception caught' ) );
			$rv = 1;
		}

		if ( $results == '' ) {
			if ( $this->Trace ) {
				$this->save_message(  _( 'Successful:' ) . ' ' . $fname . "\n" );
			}
		} else {
			$this->save_message(  '<b>' . _( 'Error:' ) . '</b> ' . $fname . ' ' . __( 'not added to the database' ) ."\n" );
			return 1;
		}
		return $rv;
	}
}

?>
