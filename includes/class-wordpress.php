<?php
/**
 * Platform specifics for the ObjtrackerDriver class.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 * PHP       version 5
 */

/**
 * WordPress specific routines within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerWordPress extends ObjtrackerDriver
{
	// Set by platform_user_get when database setup incomplete.
	/**
	 * Platform name.
	 *
	 * @var string
	 * @access private
	 * @since 1.0.3
	 */
	private $InstallUserName;

	// Set by platform_user_get when database setup incomplete.
	/**
	 * Platform name.
	 *
	 * @var string
	 * @access private
	 * @since 1.0.3
	 */
	private $InstallIsAnAdmin;

	// Set by platform_user_get when database setup incomplete.
	/**
	 * Platform name.
	 *
	 * @var ObjtrackerBadUser
	 * @access private
	 * @since 1.0.3
	 */
	private $InstallBadUser;


	/**
	 * Contructor for ObjtrackerWordPress.
	 *
	 * @since    1.0
	 * @params   $platformFacts   Array of (template/notemplate, pluginname)
	 * @returns  void
	 */
	function __construct( $templated, $name )
	{
		if ( isset( $_GET['Trace']) ) {
			$this->FirstParmPrefix = '?Trace=&page=';
			$this->Trace           = true;
		} elseif ( isset( $_POST['Trace']) ) {
			$this->Trace           = true;
			$this->FirstParmPrefix = '?Trace=&page=';
		} else {
			$this->Trace           = false;
			$this->FirstParmPrefix = '?page=';
		}
		parent::__construct();

		$this->ProductName      = __( 'Balanced Scorecard for WordPress' );
		$this->Platform         = 'WordPress';
		$this->ManagesPasswords = false;
		$this->TemplateState    = $templated;
		$this->PlatformModule   = $name;
		$this->PlatformParm     = $this->FirstParmPrefix . $this->PlatformModule . '/' . $this->PlatformModule . '.php&';
		$this->PathBase         = '';
		$this->PathConfig       = $this->PathBase . '/Config/';
		$this->AbsolutePathHelp = ABSPATH . 'wp-content/plugins/' . $this->PlatformModule . '/help';
		$this->PathHelp         = '/wp-content/plugins/' . $this->PlatformModule . '/help/';
		$this->PathInclude      = $this->PathBase . '/includes/';
		$this->PathImages       = '/wp-content/plugins/' . $this->PlatformModule . '/images/';
		$this->PathMenuImages   = '/wp-content/plugins/' . $this->PlatformModule . '/menu_script/';
		$this->PathDownload     = '/wp-content/plugins/' . $this->PlatformModule . '/Download.php';
		$this->PathDownload     = $this->FirstParmPrefix . $this->PlatformModule . '/' . $this->PlatformModule . '.php&';
		$this->PathTemplate     = $this->FirstParmPrefix . $this->PlatformModule . '/' . $this->PlatformModule . '.php&';
		$this->PathNoTemplate   = '/wp-content/plugins/' . $this->PlatformModule . '/NoTemplate.php?';
		$this->PathNoTemplate   = '/wp-content/plugins/' . $this->PlatformModule . '/objtracker.php?';

		if ( $this->TemplateState == ObjtrackerDriver::BsTemplateYes ) {
			$this->EndForm = "<input type='hidden' name='submit' value='POST' />"
								. "<input type='hidden' name='template' value='POST' />\n</form>\n";
			$this->PlatformParm .= 'template=&';
		} else {
			$this->EndForm = "<input type='hidden' name='submit' value='POST' />\n</form>\n";
		}

		if ( !defined( 'MYSQL_CLIENT_FLAGS' ) ) {
			define( 'MYSQL_CLIENT_FLAGS', 65536 );  /* Enable stored procedure support 'CLIENT_MULTI_STATEMENTS' ); */
		}
	}

	/**
	 * Return a BsUser object representing the current user.
	 *
	 * For admins, check if database parts need installating.
	 *
	 * @since    1.0
	 * @returns  BsUser           Object representing the current user.
	 */
	function platform_user_get() 
	{
		// Allowed in?
		if (!is_user_logged_in() )
			return new ObjtrackerBadUser( __( '<b>Error: </b> User must be logged on to use the Balanced Scorecard.' ) );
		
		$thisuser  = wp_get_current_user();
		$this->InstallUserName  = $thisuser->user_login;
		$this->InstallIsAnAdmin = current_user_can( $this->PlatformModule . 'admin' ) ? 'Yes' : 'No';

		// While in DbSetup frame, guide the installation	
		if ( 'Yes' == $this->InstallIsAnAdmin  && isset( $_POST['DbSetup'] ) ) {
			return $this->guided_install( $_POST['DbSetup'], '' );
		}

		// Check that there are database parts
		$this->check_db_install();
		if ( isset( $this->InstallBadUser ) ) {
			return $this->InstallBadUser;
		}

		// Create a BsUser object for this user
		$OrgID = get_current_blog_id();

		// Find user in the T_People table
		$results = $this->platform_db_query(
			'P_PersonFactsGet', '( %d, %d, %s, %s, %s )',
			array( $OrgID, 0, $this->InstallUserName, $this->InstallUserName, $this->InstallIsAnAdmin )
			);

		if ( count( $results ) == 0 ) {
			// User not found
			if ( 'Yes' != $this->InstallIsAnAdmin ) {
				// Administrator has not defined this user
				return new ObjtrackerBadUser( __( '<b>Error: </b> User <b>' ) . $this->InstallUserName . __( '</b> has not been defined to the Balanced Scorecard.' ) );
			}
			return $this->guided_install(
				'Start', 
				__( '<b>Error: </b> User <b>' ) . $this->InstallUserName . __( '</b> not automatically added to the Balanced Scorecard.  Rebuild the database components.' )
				);
		} else {
			$row    = $results[0];
			$bsuser = new ObjtrackerUser(
				$OrgID,		// org
				$row[$this->Field->C_ID],
				'No',		// not root
				$row[$this->Field->C_IsAdmin],
				$row[$this->Field->C_IsViewer],
				$this->InstallUserName,	// user
				$row[$this->Field->C_FullName],
				$row[$this->Field->C_UiSettings],
				$row[$this->Field->C_Department],
				$row[$this->Field->C_Organization],
				$row[$this->Field->C_FiscalYear1],
				$row[$this->Field->C_FiscalYearTitle],
				$row[$this->Field->C_FirstMonth],
				$row[$this->Field->C_UploadFsPath],
				$row[$this->Field->C_Trailer]
				);
		}
		$pdo = null;

		return $bsuser;
	}

	/**
	 * Check the db installation status.
	 *
	 * @since    1.0.3
	 * @returns  string	html page
	 */
	private function check_db_install()
	{
		global $wpdb;

		// Is there an install state table?
		$sqlText = 'SELECT COUNT(*) AS C_COUNT FROM information_schema.tables WHERE table_schema IN (SELECT DATABASE()) AND table_name = %s';
		$prepped = $wpdb->prepare( $sqlText, array( $this->DbPrefix . 'T_InstallState' ) );
		$rows    = $wpdb->get_results( $prepped, ARRAY_N );

		$needsupgrade = false;
		$usercount    = 0;
		if ( 0 == $rows[0][0] ) {
			if ( 'Yes' != $this->InstallIsAnAdmin ) { 
				$this->InstallBadUser = new ObjtrackerBadUser( __( '<b>Error</b> Balanced Scorecard is not yet fully installed.' ) );
				return;
			}

			$this->InstallBadUser = $this->guided_install(
				'Start', 
				__( 'Time to setup the Balanced Scorecard database components.' )
			);
			return;
		}

		$dbversion = $wpdb->get_var( 'SELECT DB_Version FROM ' . $this->DbPrefix . 'T_InstallState WHERE ID = 1' );
		if ( 1 == $dbversion ) {
			// Update the version
			$wpdb->query( 'UPDATE ' . $this->DbPrefix . 'T_InstallState SET Db_Version=2,DB_Changed=NOW() WHERE ID=1;' );
			// Update the bad proc
			include  'class-installsql.php';

			$installer = new ObjtrackerInstallSql( OBJTRACKER_CONTENT_DIR . 'sql/', 'fix:0001', $this, $this->DbPrefix );
			if ( 0 != $installer->install() ) {
				$errortext = $installer->get_messages();
				$this->InstallBadUser = $this->guided_install(
					'Start', 
				__( $errortext )
				);
			}
		}
		if ( 2 == $dbversion ) {
			// Update the bad data
			$wpdb->query( 'UPDATE ' . $this->DbPrefix . "T_InstallState SET Description='nnn%' WHERE ID='nnn%%';" );
			// Update the bad proc
			include  'class-installsql.php';

			$installer = new ObjtrackerInstallSql( OBJTRACKER_CONTENT_DIR . 'sql/', 'fix:0002', $this, $this->DbPrefix );
			if ( 0 != $installer->install() ) {
				$errortext = $installer->get_messages();
				$this->InstallBadUser = $this->guided_install(
					'Start', 
				__( $errortext )
				);
			}
			// Update the version
			$wpdb->query( 'UPDATE ' . $this->DbPrefix . 'T_InstallState SET Db_Version=3,DB_Changed=NOW() WHERE ID=2;' );
		}
		if ( 3 == $dbversion ) {
			// Update the bad data
			$wpdb->query( 'UPDATE ' . $this->DbPrefix . "T_InstallState SET Description='nnn%' WHERE ID='nnn%%';" );
			// Update the bad proc
			include  'class-installsql.php';

			$installer = new ObjtrackerInstallSql( OBJTRACKER_CONTENT_DIR . 'sql/', 'fix:0003', $this, $this->DbPrefix );
			if ( 0 != $installer->install() ) {
				$errortext = $installer->get_messages();
				$this->InstallBadUser = $this->guided_install(
					'Start', 
				__( $errortext )
				);
			}
			// Update the version
			$wpdb->query( 'UPDATE ' . $this->DbPrefix . 'T_InstallState SET Db_Version=4,DB_Changed=NOW() WHERE ID=3;' );
		}
	}
	/**
	 * Prompt user through DB setup.
	 *
	 * @since    1.0.3
	 * @param    $step           Where to start.
	 * @param    $message        Message to show.
	 * @returns  string	html page
	 */
	private function guided_install( $step, $message )
	{
		include  'class-installsql.php';

		$errortext = '';
		$nextstep  = '?';

		$steps = array(
				_( 'Start' ), __( 'The Balanced Scorecard database setup is required' ),
				'0', __( 'Clearing of prior database components for the Balanced Scorecard' ),
				'1', __( 'Creating database tables' ),
				'2', __( 'Creating database functions' ),
				'3', __( 'Creating database stored procedures' ),
				'4', __( 'Application data' ),
				'5', __( 'Add your, your organization, and your department' ),
				'6', __( 'Creating database triggers' ),
			);
					
		switch ( $step ) {
			case 'Start':
				$nextstep = '0';
				break;
			
			case '0':
				// Clear database components
				$installer = new ObjtrackerInstallSql( OBJTRACKER_CONTENT_DIR . 'sql/', 'install:0', $this, $this->DbPrefix );
				if ( 0 == $installer->install() ) {
					$nextstep = '1';
				} else {
					$errortext = $installer->get_messages();
				}
				break;

			case '1':
				// Add database tables
				$installer = new ObjtrackerInstallSql( OBJTRACKER_CONTENT_DIR . 'sql/', 'install:1', $this, $this->DbPrefix );
				if ( 0 == $installer->install() ) {
						$nextstep = '2';
				} else {
					$errortext = $installer->get_messages();
				}
				break;
				
			case '2':
				// Add database functions
				$installer = new ObjtrackerInstallSql( OBJTRACKER_CONTENT_DIR . 'sql/', 'install:2', $this, $this->DbPrefix );
				if ( 0 == $installer->install() ) {
					$nextstep = '3';
				} else {
					$errortext = $installer->get_messages();
				}
				break;

			case '3':
				// Add stored procs
				$installer = new ObjtrackerInstallSql( OBJTRACKER_CONTENT_DIR . 'sql/', 'install:3', $this, $this->DbPrefix );
				if ( 0 == $installer->install() ) {
					$nextstep = '4';
				} else {
					$errortext = $installer->get_messages();
				}
				break;

			case '4':
				// Add default data
				$installer = new ObjtrackerInstallSql( OBJTRACKER_CONTENT_DIR . 'sql/', 'install:4', $this, $this->DbPrefix );
				if ( 0 == $installer->install() ) {
					$nextstep = '5';
				} else {
					$errortext = $installer->get_messages();
				}
				break;

			case '5':
				// Add your, your organization, and your department
				// Create a BsUser object for this user
				$OrgID = get_current_blog_id();

				// Find user in the T_People table
				$results = $this->platform_db_query(
					'P_PersonFactsGet', '( %d, %d, %s, %s, %s )',
					array( $OrgID, 0, $this->InstallUserName, $this->InstallUserName, $this->InstallIsAnAdmin )
					);

				if ( count( $results ) > 0 ) {
					$nextstep = '6';
				} else {
					$errortext = 'Error in database setup, unable to add your userid.';
				}
				break;

			case '6':
				// Create database triggers
				$installer = new ObjtrackerInstallSql( OBJTRACKER_CONTENT_DIR . 'sql/', 'install:6', $this, $this->DbPrefix );
				if ( 0 == $installer->install() ) {
					$nextstep = 'Done';
				} else {
					$errortext = $installer->get_messages();
				}
				break;

			default:
		}

		// Create the guided page's steps with status
		$table   = "<br /><div id='BssNonNavigationPanel'>\n";
		$table  .= $message . "<br />\n";
		$table  .= "<table class='BssGridview'>\n";
		$table  .= "<tr class='BssGvHeader'><th>#</th><th>Step</th><th>Status</th></tr>\n";
		$status  = 'Succeeded';
		$oddeven = 'Odd';
		for ( $i = 0; $i < count( $steps ); $i += 2 ) {
			$table  .= "<tr class='BssGv" . $oddeven . "Row'><td>" . $steps[$i] . '</td><td>' . $steps[$i + 1];
			$oddeven = $oddeven == 'Odd' ? 'Even' : 'Odd';
			if ( $steps[$i] == $step ) {
				if ( $step == 'Start' ) {
					$table .= "</td><td>Starting</td></tr>\n";
				} elseif ( $nextstep == '?' ) {
					$table .= "</td><td><b>failed</b></td></tr>\n";
				} else {
					$table .= "</td><td><b>Succeeded</b></td></tr>\n";
				}
				$status = '&nbsp;';
			} elseif ( $steps[$i] == 'Start' ) {
				$table .= "</td><td>&nbsp;</td></tr>\n";
			} else {
				$table .= '</td><td>' . $status . "</td></tr>\n";
			}
		}
		$table .= "</table>\n";

		if ( $nextstep == '?' ) {
			// error page
			$text =
				$table . "<br />\n"
				. $this->platform_start_form( '', '' )
				. "<input type='hidden' name='DbSetup' value='Start' />\n"
				. "<input type='submit' name='submit' value='" . __( 'Click to restart' ) . "' class='BssButton' />\n"
				. $this->EndForm 
				. '<b>' . __( 'ERROR:' ) . '</b> ' . __( 'database initialization failed, see messages below.' ) 
				. '<br /><br />' . $errortext . "<br />\n</div>\n";
		} elseif ( $nextstep == 'Done' ) {
			// complete page
			$text =
				$table . "<br />\n"
				. "<br /><br />\n"
				. __( 'The installation process is now complete, time to set beginning month of the fiscal year by clicking Organization from the Admin menu.' ) . '<br />'
				. '<br />' . "<a href='" . $this->PlatformParm . "sc_menu=Alerts'>End setup</a>\n" . '<br />';
		} else {
			// continue page
			$text =
				$table . "<br />\n"
				. "<script type='text/javascript'> function OneClickOnly(form) { \n"
				. "   form.objtrackerdb.disabled=true; \n"
				. "   form.objtrackerdb.value='Please wait...'; \n"
				. "   return true; \n"
				. "} </script> \n"
				. $this->platform_start_form( '', '' )
				. "<input type='hidden' name='DbSetup' value='" . $nextstep . "' />\n"
				. "<input type='submit' name='objtrackerdb' value='" . __( 'Click to continue' )
				. "' onclick=\"OneClickOnly(this.form);\" class='BssButton' />\n"
				. $this->EndForm . "<br />\n</div>\n"; 
		}
		return new ObjtrackerBadUser( $text );
	}

	/**
	 * Prompt user through DB setup.
	 *
	 * @since    1.0.3
	 * @returns  string	html page
	 */
	private function db_setup( )
	{
		return new ObjtrackerBadUser( __( '<b>Error: There is a problem with your installation.' ) );
	}

	/**
	 * Set environmental variables based on the page requested.
	 *
	 * @since    1.0
	 * @param    $menuName        Name of page to return.
	 * @returns  void
	 */
	public function platform_set_menuname( $menuName )
	{
		$bsuser = $this->BsUser;

		if ( $bsuser->is_root() ) {
			$menuName = 'Admin-Orgs';
		}
		$this->MenuName = $menuName;
		$trace          = $this->Trace ? '&Trace=' : '';

		// To switch back and forth between have templates and having none (requires ...).
		$this->TemplateLinkYes = " | <a id='HyperLinkT' class='Bssmplink' href='"
				. $this->PathNoTemplate . 'sc_menu=Alerts' . $trace . "'>" . __( 'No Template' ) . "</a>\n";
		$this->TemplateLinkNo  = " | <a id='HyperLinkT' class='Bssmplink' href='/wp-admin/tools.php"
				. $this->FirstParmPrefix . 'OBJTRACKERV0x1/OBJTRACKERV0x1.php&sc_menu=Alerts' . $trace . "&template=yes'>" . __( 'Template' ) . "</a>\n";

		// To turn off theme templates.
		$this->TemplateLinkYes = " | <a id='HyperLinkT' class='Bssmplink' href='/'>" . __( 'Home' ) . "</a>\n";
		$this->TemplateLinkNo  = " | <a id='HyperLinkT' class='Bssmplink' href='/'>" . __( 'Home' ) . "</a>\n";

		if ( strlen( $menuName ) > 6 && substr( $menuName, 0, 6 ) == 'Admin-' ) {
			$bareName             = substr( $menuName, 6 ); // "Departments" of "Admin-Departments"
			$this->ModuleName     = $bareName . '.php';
			$this->ModulePath     = $this->TemplateState == ObjtrackerDriver::BsTemplateYes
										? WP_CONTENT_DIR . '/plugins/' . $this->PlatformModule . '/config/' . $this->ModuleName
										: 'config/' . $this->ModuleName;
			$this->ModuleFunction = 'bs_' . strtolower( $bareName );
		} else {
			$this->ModuleName     = $menuName . '.php';
			$this->ModulePath     = $this->TemplateState == ObjtrackerDriver::BsTemplateYes
										? WP_CONTENT_DIR . '/plugins/' . $this->PlatformModule . '/nonconfig/' . $this->ModuleName
										: 'nonconfig/' . $this->ModuleName;
			$this->ModuleFunction = 'bs_' . strtolower( $menuName );
		}
	}

	/**
	 * Return <form> tag. 
	 *
	 * @since    1.0
	 * @param    $additional       Value to make hash better.
	 * @returns  string            Hash value
	 */
	public function platform_start_form( $urlparms, $additional ) {
		return "<form class='BssForm' action='" . $this->PathBase . $this->PlatformParm
			. 'sc_menu=' . $this->MenuName . $urlparms . "' method='post' " . $additional . " > <!-- objtracker-Form -->\n"
			. "<input type='hidden' name='token' value='" . wp_create_nonce( 'objtrackerform' ) . "' />\n";
	}

	/**
	 * Return token to help prevent against CSRF attacks. 
	 *
	 * @since    1.0
	 * @param    $additional       Value to make hash better.
	 * @returns  string            Hash value
	 */
	public function platform_get_token( $additional ) {
		return wp_create_nonce( $additional );
	}

	/**
	 * Return token to help prevent against CSRF attacks. 
	 *
	 * @since    1.0
	 * @param    $tokenvalue       Value from get token.
	 * @param    $additional       Value to make hash better.
	 * @returns  boolean           True if valid
	 */
	public function platform_test_token( $tokenvalue, $additional ) {
		return wp_verify_nonce( $tokenvalue, $additional );
	}

	/**
	 * Return token to help prevent against CSRF attacks. 
	 *
	 * @since    1.0
	 * @param    $additional       Value to make hash better.
	 * @returns  string            Access denigned
	 */
	public function platform_access_denied() {
		die( __( 'Security check' ) );
	}

	/**
	/**
	 * Query database using stored procedure name and array of parameters.
	 *
	 * @since    1.0
	 * @param    string    $storedProcedure    Stored procedure name
	 * @param    array     $parmArray          Array of parameters
	 * @return   array                         Database results
	 */
	public function platform_db_query( $storedProcedure, $placeholders, $values )
	{
		global $wpdb;
	//	$wpdb->show_errors(); 
		$wpdb->db_connect();
		// Please don't laugh, beats modifying wordpress
		$sqlText = 'CALL ' . $this->DbPrefix . $storedProcedure . $placeholders;

		$prepped    = $wpdb->prepare( $sqlText, $values );
		$gv1Results = $wpdb->get_results( $prepped, OBJECT );

		$this->DbData = array();
		$rowid        = 0;
		foreach ( $gv1Results as $row ) {
			$newColumns = array();
			$columnid   = 0;
			foreach ( $row as $columns ) {
				$newColumns[$columnid] = $columns;
				$columnid++;
			}
			$this->DbData[$rowid] = $newColumns;
			$rowid++;
		}

		$this->DbColumns = $wpdb->get_col_info();
		if ( is_array( $this->DbColumns ) ) {
			// $this->trace_text( '<br />' );
			// foreach ( $this->DbColumns as $cn ) {
			//	$this->trace_text( ' c=' . $cn );
			// }
			// $this->trace_text( '<br />' );
		} else {
			$this->trace_text( '<br /><b> no columns=' . $sqlText . '</b><br />' );
		}
		$this->Field = new ObjtrackerDbField( $this->DbColumns );
		$wpdb->flush();

		return $this->DbData;
	}

	/**
	 * Query database using stored procedure name and array of parameters.
	 *
	 * @since    1.0
	 * @param    string    $sqltext    text to add to the database
	 * @return   array                 Database results
	 */
	public function platform_db_install( $sqltext )
	{
		global $wpdb;
	//	$wpdb->show_errors(); 
		$wpdb->suppress_errors( false ); 
		$wpdb->db_connect();

		$results = $wpdb->query( $sqltext );
		if ( $results === false ) {
			return 'failure';
		} else {
			return '';
		}
	}
} // End ObjtrackerWordPress
?>
