<?php
/**
 * Class manages decoding urls, invoking the requested sc_menu page, writting menu, and trailer.
 *
 * This class must be sub-classed, depending on the current platform by
 * ObjtrackerWordPress, ObjtrackerDrupal, or ... which contain platform specific code.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */
/**
 * ObjtrackerDriver processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerDriver
{
	// Set by constructor
	/**
	 * Product name.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $ProductName;

	// Set by constructor
	/**
	 * Platform name.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $Platform;

	/**
	 * Holds array of strings traced.
	 *
	 * @var array
	 * @access public
	 * @since 1.0
	 */
	private $TraceArray;		// Array of text

	/**
	 * True/false indicates if tracing is on. Use &Trace= in URL to set tracing on.
	 *
	 * @var bool
	 * @access public
	 * @since 1.0
	 */
	public $Trace;				// do trace true/false

	/**
	 * Holds platform dependent string that may participate in construction of URLs.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PlatformModule;

	/**
	 * Holds platform dependent string that may participate in construction of URLs.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathBase;

	/**
	 * Holds platform dependent string that may participate in construction of URLs.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PlatformParm;

	/**
	 * Holds platform dependent path to config directory.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathConfig;

	/**
	 * Holds platform dependent path to the help directory.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $AbsolutePathHelp;

	/**
	 * Holds platform dependent path to the help directory.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathHelp;

	/**
	 * Holds platform dependent string to the include directory.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathInclude;

	/**
	 * Holds platform dependent string to the images directory.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathImages;

	/**
	 * Holds platform dependent string when no platform template desired.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathNoTemplateBase;

	/**
	 * Holds platform dependent string for extracts and downloading documents.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathDownload;

	/**
	 * Holds platform dependent string to the menu images directory.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathMenuImages;

	/**
	 * Holds platform dependent string for URLs for pages with platform templates.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathTemplate;

	/**
	 * Holds platform dependent string for URLs for pages with no platform templates.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $PathNoTemplate;

	/**
	 * False, don't intend to manage passwords.
	 *
	 * @var bool
	 * @access public
	 * @since 1.0
	 */
	private $ManagesPasswords;

	/**
	 * Holds fragment of html for terminating a form while maintaining state.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $EndForm;

	/**
	 * Holds platform dependent string for URLs specifying the first URL parameter prefix.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $FirstParmPrefix;

	/**
	 * Indicates type of request, see BsTemplate* constants.
	 *
	 * @var char
	 * @access public
	 * @since 1.0
	 */
	private $TemplateState;
	const BsTemplateYes      = 'y';
	const BsTemplateNo       = 'n';
	const BsTemplateDownload = 'd';

	/**
	 * The html link fragment appropriate for turning on templates.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $TemplateLinkYes;	// Template Yes/No above menu bar html string

	/**
	 * The html link fragment appropriate for turning off templates.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $TemplateLinkNo;

	// Derived from SetMenuName( $menuName )
	/**
	 * Holds the user object.
	 *
	 * @var BsUser
	 * @access private
	 * @since 1.0
	 */
	private $BsUser;

	/**
	 * Holds name of the menu selection or page selection in URL.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $MenuName;

	/**
	 * Holds platform dependent string name of the module.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $ModuleName;

	/**
	 * Holds platform dependent string for the path including module.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $ModulePath;

	/**
	 * Holds name of the function that will process the page request.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $ModuleFunction;

	/**
	 * Page action derived from URL or post data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	private $Action;

	/**
	 * Column to be sorted.
	 *
	 * @var int
	 * @access public
	 * @since 1.0
	 */
	private $SortField;

	/**
	 * True/false indicating was entered due to a post.
	 *
	 * @var bool
	 * @access public
	 * @since 1.0
	 */
	private $IsPost;

	/**
	 * Html fragment for a Submit button/link.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Input0SubmitAdd;

	/**
	 * Html fragment for a Submit button/link.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Input0SubmitSave;

	/**
	 * Html fragment for a Submit button/link.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Input1SubmitSave;

	/**
	 * Html fragment for a Submit button/link.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Input0SubmitUpdate;

	/**
	 * Html fragment for a Submit button/link.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Input1SubmitUpdate;

	/**
	 * Html fragment for a Submit button/link.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Input2SubmitUpdate;

	/**
	 * Html fragment for a Submit button/link.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Input0SubmitCancel;

	/**
	 * Html fragment for a Submit button/link.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Input1SubmitCancel;

	/**
	 * Html fragment for a Submit button/link.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Input2SubmitCancel;

	/**
	 * Pages such as Objective have multiple forms. This field indicates the form that the request came from.
	 *
	 * @var int
	 * @access public
	 * @since 1.0
	 */
	public $Panel;
	
	/**
	 * URL parameter fragment for maintaining sort state etc.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $PageState;

	/**
	 * Indicates if primary description to be displayed.
	 *
	 * @var char
	 * @access public
	 * @since 1.0
	 */
	private $ShowHead;

	/**
	 * The string that prepends all stored procedures and when database objects created, all function, tables, ...
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $DbPrefix;

	/**
	 * Retrieved database data from query.
	 *
	 * @var array
	 * @access public
	 * @since 1.0
	 */
	public $DbData;

	/**
	 * Array of keys of the database queries' columns.
	 *
	 * @var array
	 * @access public
	 * @since 1.0
	 */
	public $DbColumns;

	/**
	 * Object holds the name/ids of database columns.
	 *
	 * @var BsDbField
	 * @access public
	 * @since 1.0
	 */
	public $Field;

	/**
	 * Indicates if current show/hide panel should be shown.
	 *
	 * @var char
	 * @access public
	 * @since 1.0
	 */
	private $ShowInfo;

	/**
	 * Not set or array of install database messages.
	 *
	 * @var char
	 * @access protected
	 * @since 1.0
	 */
	protected $InstallMessages;

	/**
	 * Constructor for ObjtrackerDriver class.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function __construct()
	{
		$this->DbPrefix   = 'objtracker';
		$this->TraceArray = array();
	}

	/**
	 * Retrieve class variable
	 *
	 * @since    1.0
	 * @param    string  $property    Instance variable name.
	 * @return   various              The value of the instance variable.
	 */
	public function __get( $property )
	{
		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}
	}

	/**
	 * Set a class variable
	 *
	 * Long Description
	 *
	 * @hook     action 'admin_notices'
	 * @since    1.0
	 * @param    string    $property    Instance variable name.
	 * @param    any       $value       Instance variable value.
	 * @return   void
	 */
	public function __set( $property, $value )
	{
		if ( property_exists( $this, $property ) ) {
			$this->$property = $value;
		}
		return $this;
	}

	/**
	 * Test if instance value is set.
	 *
	 * @since    1.0
	 * @param    string    $property    Instance variable name.
	 * @return   void
	 */
	public function __isset( $property )
	{
		return isset( $this->data[$property] );
	}

	/**
	 * Unset an instance variable
	 *
	 * @since    1.0
	 * @param    string    $property    Instance variable name.
	 * @return   void
	 */
	public function __unset( $property )
	{
		unset( $this->data[$property] );
	}

	/**
	 * Drive the formation of all output for the plugin.
	 *
	 * Outputs the main administration screen, and handles installing/upgrading, saving, and deleting.
	 *
	 * @since    1.0
	 * @param    object  $bsuser        The user object
	 * @param    object  $outsideMenu   Name of alternate initial menu
	 * @return   string              Page's html text.
	 */
	function balanced_scorecard( $bsuser, $outsideMenu )
	{
		$this->BsUser = $bsuser;

		$task                     = 'list';
		$sortField                = '';
		$type                     = '?';
		$this->Input0SubmitAdd    = " <input type='submit' name='submit0Add' value='" . __( 'Add' ) . "' class='BssButton'/>\n";
		$this->Input0SubmitSave   = " <input type='submit' name='submit0Add' value='" . __( 'Save' ) . "' class='BssButton'/>\n";
		$this->Input1SubmitSave   = " <input type='submit' name='submit1Add' value='" . __( 'Save and attach documents' ) . "' class='BssButton'/>\n";
		$this->Input0SubmitUpdate = " <input type='submit' name='submit0Update' value='" . __( 'Update' ) . "' class='BssButton' />\n";
		$this->Input1SubmitUpdate = " <input type='submit' name='submit1Update' value='" . __( 'Update' ) . "' class='BssButton' />\n";
		$this->Input2SubmitUpdate = " <input type='submit' name='submit2Update' value='" . __( 'Update' ) . "' class='BssButton' />\n";
		$this->Input3SubmitUpdate = " <input type='submit' name='submit3Update' value='" . __( 'Update' ) . "' class='BssButton' />\n";
		$this->Input0SubmitCancel = " <input type='submit' name='submit0Cancel' value='" . __( 'Cancel' ) . "' class='BssButton' />\n";
		$this->Input1SubmitCancel = " <input type='submit' name='submit1Cancel' value='" . __( 'Cancel' ) . "' class='BssButton' />\n";
		$this->Input2SubmitCancel = " <input type='submit' name='submit2Cancel' value='" . __( 'Cancel' ) . "' class='BssButton' />\n";

		$this->IsPost = isset( $_POST['submit'] ) ? true : false;

		if ( isset( $_POST[ 'submit' ] ) ) {
			// All forms have a token hidden value
			if ( !$this->platform_test_token( $_POST['token'], 'objtrackerform' ) ) {
				return $this->platform_access_denied();
			}

			$this->TemplateState = isset( $_POST[ 'template' ] ) ? ObjtrackerDriver::BsTemplateYes : ObjtrackerDriver::BsTemplateNo;
			// FORM data
			if ( isset( $_POST['submit0Add'] ) ) {
				$task        = 'add';
				$this->Panel = '0';
			} elseif ( isset( $_POST['submit1Add'] ) ) {
				$task        = 'add';
				$this->Panel = '1';
			} elseif ( isset( $_POST['submit0Update'] ) ) {
				$task        = 'update';
				$this->Panel = '0';
			} elseif ( isset( $_POST['submit1Update'] ) ) {
				$task        = 'update';
				$this->Panel = '1';
			} elseif ( isset( $_POST['submit2Update'] ) ) {
				$task        = 'update';
				$this->Panel = '2';
			} elseif ( isset( $_POST['submit0Cancel'] ) ) {
				$this->Panel = '0';
				$task        = 'cancel';
			} elseif ( isset( $_POST['submit1Cancel'] ) ) {
				$task        = 'cancel';
				$this->Panel = '1';
			} elseif ( isset( $_POST['submit2Cancel'] ) ) {
				$task        = 'cancel';
				$this->Panel = '2';
			} elseif ( isset( $_POST['submitChange'] ) ) {
				$task        = 'change';
				$this->Panel = '1';
			} else {
				$task        = 'l'; //strtolower( $_POST['submitButton'] );
				$this->Panel = '1';
			}
			$this->platform_set_menuname( $_POST[ 'sc_menu' ] );
			$type = 'POST submitButton';
			$this->trace_text( '<br />bsdriver post(' . $task . $this->Panel . ' ' . $this->Action . ')' );
		} elseif ( isset( $_GET[ 'sc_menu' ] ) ) {
			// See if user is showing or hiding
	//		$this->trace_text( 'Method(GET) ' . $_SERVER['HTTP_HOST']. ' ' . $_SERVER['REQUEST_URI'] );
			$this->trace_text( ' ui=' . $bsuser->UiSettings );
			if ( isset( $_GET['sh']) && $bsuser->should_update( ObjtrackerUser::UIShowInfo, $_GET['sh'] ) ) {
				$this->platform_db_query(
						'P_PersonUpdateUI',
						'( %d, %d, %s )',
						array( $bsuser->OrgID, $bsuser->ID, $bsuser->UiSettings )
						);
				$this->trace_text( ' ui=' . $bsuser->UiSettings );
			}
			// See if user changing to or from platform templates
			$this->TemplateState = isset( $_GET[ 'template' ] ) ? ObjtrackerDriver::BsTemplateYes : ObjtrackerDriver::BsTemplateNo;
		//	$this->trace_text( 'Method(GET) ' . $_SERVER['HTTP_HOST']. ' ' . $_SERVER['REQUEST_URI'] );

			// URL data
			$this->platform_set_menuname( $_GET[ 'sc_menu' ] );
			if ( isset( $_GET[ 'sc_action' ] ) ) {
				$type = 'GET sc_action';
				$task = strtolower( $_GET['sc_action'] );

				if ( $_GET['sc_action'] == 'sort' ) {
					$sortField = $_GET[ 'fld' ];
				}
			} elseif ( isset( $_GET[ 'submitButton' ] ) ) {
				$type = 'GET submitButton';
				$task = strtolower( $_GET['submitButton'] );
			} else {
				$type = 'GET what?';
			}
			if ( isset( $_GET['p'] ) )
				$this->Panel = $_GET['p'];
			else
				$this->Panel = '0';
			$this->trace_text( 'Action(' . $task . ' ' . $this->Panel . ')' );
		} elseif ( $outsideMenu != '' ) {
		//	$this->TemplateState = ObjtrackerDriver::BsTemplateYes;
			$type        = 'External menu';
			$this->Panel = '0';
			$this->trace_text( 'Method(GET2)' );
			$this->platform_set_menuname( $outsideMenu );
		} else {
		//	$this->TemplateState = ObjtrackerDriver::BsTemplateYes;
			$type        = 'No URL parms';
			$this->Panel = '0';
			$this->trace_text( 'Method(GET1)' );
			$this->platform_set_menuname( 'Alerts' );
		}

		$trace = $this->Trace
			? "<input type='hidden' name='Trace' value='value' />\n"
			: '';

		$menu_body = $this->show_menu();
		if ( 1 == 2 || $this->ModuleName == '.php' ) {
			$page_body = 'MenuName is ......' . $this->MenuName
						. '<br />ModuleName is ....' . $this->ModuleName
						. '<br />ModulePath is ....' . $this->ModulePath
						. '<br />ModuleFunction is.' . $this->ModuleFunction
						. '<br />Templated.........' . $this->TemplateState
						. '<br />Task .............' . $task
						. '<br />Entered as .......' . $type
						. '<br />';
		} else {
			$this->PageState = '';

			if ( isset( $this->InstallMessages ) ) {
				$page_body = implode( $this->InstallMessages, '<br />' );
			} else {
				// Include module
				require_once $this->ModulePath;

				// Call modules entry function
				$this->Action    = $task;
				$this->SortField = $sortField;
				$page_body       = $function_body = call_user_func(
					$this->ModuleFunction,	// function name
						$this,				// Parm: 1 - Environmentals for WordPress, Drupal, ...
						$bsuser				// Parm: 2 - The user object
					);
				if ( isset( $_GET['mimetype'] ) ) {
					return $this->page_body;
				}
			}
		}

		return
			"<div id='BssWorld'> <!-- Start BssWorld -->\n"
			. "<script type='text/javascript'> var pathIMAGE ='" . $this->PathMenuImages . "';</script>\n"
			. $menu_body
			. "<div id='BssNonNavigationPanel'>\n"
			. $page_body
			. $this->show_trace()
			. $this->show_trailer( $bsuser )
			. "</div> <!-- end BssNonNavigationPanel -->\n"
			. "</div> <!-- end BssWorld -->\n";
	}


	/**
	 * Return the plugin trailer text
	 *
	 * @since    1.0
	 * @param    object  $bsuser   The BsUser object.
	 * @return   string            The plugin trailer text.
	 */
	public function show_trailer( $bsuser )
	{
		return
			"<div class='BssFooter'>\n"
			. ObjtrackerEasyStatic::table_start( "style='width: 800px; line-height: 14px; font-size: xx-small;'" )
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td( "colspan='3'", '<hr />' )
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td( "style='text-align: left; width: 25%'", $bsuser->Trailer )
			. ObjtrackerEasyStatic::table_td( '',  $bsuser->Organization )
			. ObjtrackerEasyStatic::table_td( "style='text-align: right; width: 25%'", date( 'Y-m-d' ) ) 
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_end()
			. "</div><!-- end BssFooter -->\n";
	}

	/**
	 * Trace database columns.
	 *
	 * @since    1.0
	 * @return   void
	 */
	public function db_dump_columns( $columnInfo )
	{
		$this->trace_text( ' dbcolumns' );
		foreach ( $columnInfo as $c ) {
			$this->trace_text( '(' . $c->name . ')' );
		}
	}

	/**
	 * Trace database response.
	 *
	 * @since    1.0
	 * @return   void
	 */
	public function db_dump_data( $results )
	{
		$this->trace_text( ' dbdata' );
		foreach ( $results as $r ) {
			foreach ( $r as $rc ) {
				$this->trace_text( '(' . $rc . ')' );
			}
		}
	}

	/**
	 * Set a title
	 *
	 * @since    1.0
	 * @param    string    $txt    Title to be shown.
	 * @return   string            Html segment.
	 */
	public function title_heading( $txt )
	{
		return
			"<div class='BssInfoHeader'> <!-- title_heading -->\n"
			. " <div class='BssInfoLine'><div class='BssInfoLeft'>" . $txt . "</div></div>\n"
			. "</div> <!-- end title_heading -->\n";
	}

	/**
	 * Set the description with this title.
	 *
	 * @since    1.0
	 * @param    string    $title    Title.
	 * @param    string    $setting  Owner of description section.
	 * @param    string    $parm     Page specific parameter
	 * @return   type                Description
	 * @return   void
	 */
	public function set_description_head( $title, $setting, $parm )
	{
		$bsuser = $this->BsUser;

		switch ( $setting ) {
			case ObjtrackerUser::UIShowInfo:
				$this->ShowInfo = substr( $bsuser->UiSettings, $setting, 1 );
				$key            = '&sh=';
				break;
			case ObjtrackerUser::UIShowDetail:
				$key = '&sd=';
				break;
			case ObjtrackerUser::UIShowTargets:
				$key = '&st=';
				break;
			case ObjtrackerUser::UIShowMeasurements:
				$key = '&sm=';
				break;
		}

		// Set styles based on state
		$this->ShowHead = substr( $bsuser->UiSettings, $setting, 1 );
		if ( $this->ShowHead == ObjtrackerUser::UIShowYes ) {
			$sh_show = "style='display:none;'";				// hide show button
			$sh_hide = "style='display:block;'"; 			// show hide button, show section

			return
			"<div class='BssInfoHeader'> <!-- set_description_head -->\n"
			. " <div class='BssInfoLine'>\n"
			. "	<div class='BssInfoLeft'>" . $title . "</div>\n"
			. "	<a class='BssInfoRight' " . $sh_hide . " href='"
			. $this->PlatformParm . 'sc_menu=' . $this->MenuName . $this->PageState . $parm . $key . ObjtrackerUser::UIShowNo . "'>\n"
			. "  <img src='" . $this->PathImages . "collapse.jpg' alt='Hide'/></a>\n"
			. " </div>\n"
			. "</div><!-- end set_description_head -->\n"
			. "<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
			. "<div id='BssDescription_ContentPanel' " . $sh_hide . " >\n"
			;
		} else {
	//		$this->trace_text( '<br />Show=False<br />' );
			$sh_show = "style='display:block;'"; // show show button
			$sh_hide = "style='display:none;'"; // hide hide button,hide section
			return
			"<div class='BssInfoHeader'> <!-- Scorecard-Description -->\n"
			. " <div class='BssInfoLine'>\n"
			. " <div class='BssInfoLeft'>" . $title . "</div>\n"
			. " <a class='BssInfoRight'" . $sh_show . " href='" . $this->PlatformParm . 'sc_menu=' . $this->MenuName
			. $this->PageState . $parm . $key . ObjtrackerUser::UIShowYes . "'>\n"
			. " <img src='" . $this->PathImages . "expand.jpg' alt='Show'/></a>\n"
			. "</div>\n"
			. "</div><!-- endset_description_head -->\n"
			;
		}

		// Return Show/Hide header
	}
	/**
	 * Format header description text
	 *
	 * @since    1.0
	 * @param    type    $varname    Text to display
	 * @return   type                Html seqment that formats input.
	 */
	public function description_headertext( $txt )
	{
		return
			"<div class='BssInfoHeader'> <!-- description_headertext -->\n"
			. " <div class='BssInfoLine'>\n"
			. "   <div class='BssInfoLeft'>" . $txt . "</div>\n"
			. " </div>\n"
			. "</div><!-- end description_headertext -->\n";
	}

	/**
	 * Format descripton
	 *
	 * Long Description
	 *
	 * @hook     action 'admin_notices'
	 * @since    1.0
	 * @param    string    $text     Text to be added to the description
	 * @return   type                Html seqment that formats input.
	 */
	public function description_text( $text )
	{
		if ( $this->ShowInfo == ObjtrackerUser::UIShowYes ) {
			return "<p class='BssDescription'>\n" . $text . "\n</p>\n";
		} else {
			return '';
		}
	}

	/**
	 * Start an unordered list for a description.
	 *
	 * @since    1.0
	 * @return   type                Html seqment that formats UL.
	 */
	public function description_list_start()
	{
		if ( $this->ShowInfo == ObjtrackerUser::UIShowYes ) {
			return "<ul class='BssUL'>\n";
		} else {
			return '';
		}
	}
	/**
	 * Format a UL item for a description
	 *
	 * @since    1.0
	 * @param    string    $text     Text to be added to the UL description
	 * @return   string              Html seqment that formats input.
	 */
	public function description_list_item( $text ) 
	{
		if ( $this->ShowInfo == ObjtrackerUser::UIShowYes ) {
			return "<li>\n" . $text . "</li>\n";
		} else {
			return '';
		}
	}

	/**
	 * Format a UL item for a description
	 *
	 * @since    1.0
	 * @return   string                Html seqment that formats end UL.
	 */
	public function description_list_end()
	{
		if ( $this->ShowInfo == ObjtrackerUser::UIShowYes ) {
			return "</ul>\n";
		} else {
			return '';
		}
	}


	/**
	 * Format a close of a description
	 *
	 * @since    1.0
	 * @return   string                Html seqment that formats end of description.
	 */
	public function description_tail()
	{
		if ( $this->ShowHead == ObjtrackerUser::UIShowYes ) // description visible
			return "</div> <!-- end description_tail -->\n";
		else
			return '';
	}

	/**
	 * When &Trace= added to url, show trace information	
	 *
	 * @since    1.0
	 * @return   string                Formated trace information
	 */
	public function show_trace()
	{
		$txt = '';
		if ( $this->Trace && count( $this->TraceArray ) > 0 ) {
			$txt .= "\n<br />";
			$txt .= "<div><!--start trace -->\n";
			foreach ( $this->TraceArray as $item ) {
				$txt .= $item . "\n";
			}
			$txt .= "</div><!--end trace -->\n";
		}

		$this->TraceArray = array();
		return $txt;
	}

	/**
	 * Format the menu.
	 *
	 * @since    1.0
	 * @return   string                The plugin menu
	 */
	public function show_menu()
	{
		$bsuser = $this->BsUser;
		//$this->trace_text( 'scorecard_menus ' );

		if ( isset( $_GET['NoMenu'] ) )
			return '';

		$templatelink = ( $this->TemplateState == ObjtrackerDriver::BsTemplateYes ) ? $this->TemplateLinkYes : $this->TemplateLinkNo;

		$menu_header1 = "<div id='BssBanner'>\n"
			. ObjtrackerEasyStatic::table_start( "class='Bssbackground'" )
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td( '', "<img src='" . $this->PathImages . "BalancedScorecard.png' alt='Balanced Scorecard'/></a>" )
			. ObjtrackerEasyStatic::table_td( "class='BssBanner1'", $this->ProductName )
			. ObjtrackerEasyStatic::table_td( "class='BssBanner2'", __( 'Fiscal Year' ) . ' ' . $bsuser->FiscalYearTitle )
			. ObjtrackerEasyStatic::table_td(
					"class='BssBanner2'",
					$bsuser->FullName . ' ('
						. $bsuser->UserName . ')&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
						. $templatelink
						. " | <a  class='Bssmplink' href='" . $this->PlatformParm . "sc_menu=Help'>" . __( 'Help' ) . '</a> |&nbsp;'
						)
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_end()
			. "</div><!-- end BssBanner -->\n";

		if ( $bsuser->is_root() )
			return $menu_header1;

		$menu_header2 = "<div style='position:relative;left:0px;top:0px;'><br /><br /><div id='BssMenuSystem'>\n"
			. ObjtrackerEasyStatic::table_start( "id='BssMenuTable'" )
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::menu_tdtabletrth( $this, __( 'Alerts' ), 'Alerts' )
			. ObjtrackerEasyStatic::menu_tdtabletrth( $this, __( 'Objectives' ), 'Objectives' )
			. ObjtrackerEasyStatic::menu_tdtabletrth( $this, __( 'Dashboard' ), 'Dashboard' )
			. ObjtrackerEasyStatic::menu_tdtabletrth( $this, __( 'Baseline' ), 'Baseline' );

		$menu_admin = ( $bsuser->is_admin() )
				? ObjtrackerEasyStatic::table_td(
					'',
					ObjtrackerEasyStatic::table_start( '' )
					. ObjtrackerEasyStatic::table_tr_start( '' )
					. ObjtrackerEasyStatic::table_th( '', __( 'Admin...' ) )
					. ObjtrackerEasyStatic::table_tr_end()
					. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Departments' ), 'Admin-Departments' )
					. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Fiscal Years' ), 'Admin-FiscalYears' )
					. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Frequency' ), 'Admin-Frequency' )
					. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Metric Types' ), 'Admin-MetricTypes' )
					. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Objective Types' ), 'Admin-ObjectiveTypes' )
					. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Organization' ), 'Admin-Organization' )
					. ObjtrackerEasyStatic:: menu_trtd( $this, __( 'People' ), 'Admin-People' )
					. ObjtrackerEasyStatic::table_tr_start( '' )
					. ObjtrackerEasyStatic::table_td( '', '<hr />' ) 
					. ObjtrackerEasyStatic::table_tr_end()
					. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Extend End FY' ), 'Admin-ExtendEndFy' )
					. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Table Info' ), 'Admin-TableInfo' )
					. ObjtrackerEasyStatic::table_end()
					)
					. ObjtrackerEasyStatic::table_td(
						'',
						ObjtrackerEasyStatic::table_start( '' )
						. ObjtrackerEasyStatic::table_tr_start( '' )
						. ObjtrackerEasyStatic::table_th( '', __( 'Audit...' ) )
						. ObjtrackerEasyStatic::table_tr_end()
						. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Mine today' ), 'AuditIndex&Who=My&What=Day' )
						. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Mine 3 month' ), 'AuditIndex&Who=My&What=3Months' )
						. ObjtrackerEasyStatic::menu_trtd( $this, __( 'All 3 months' ), 'AuditIndex&Who=All&What=3Months' )
						. ObjtrackerEasyStatic::menu_trtd( $this, __( 'Recent by user' ), 'AuditWho&' )
						. ObjtrackerEasyStatic::table_end()
					)
			:
				ObjtrackerEasyStatic::table_td(
					'',
					ObjtrackerEasyStatic::table_start( '' )
						. ObjtrackerEasyStatic::table_tr_start( '' )
							. ObjtrackerEasyStatic::table_th( '', '&nbsp;' )
						. ObjtrackerEasyStatic::table_tr_end()
					. ObjtrackerEasyStatic::table_end()
				)
				. ObjtrackerEasyStatic::table_td(
					'',
					ObjtrackerEasyStatic::table_start( '' )
						. ObjtrackerEasyStatic::table_tr_start( '' )
							. ObjtrackerEasyStatic::table_th( '', '&nbsp;' )
						. ObjtrackerEasyStatic::table_tr_end()
					. ObjtrackerEasyStatic::table_end()
				);

		$menu_tail = ObjtrackerEasyStatic::table_tr_end()
					. ObjtrackerEasyStatic::table_end()
					. "</div></div><!-- end BssMenuSystem -->\n" ;

		return $menu_header1 . $menu_header2 . $menu_admin . $menu_tail;
	}

	/**
	 * Return a formatted an error message.
	 *
	 * @since    1.0
	 * @param    string    $message    Message text.
	 * @return   string                Formated error message.
	 * @return   void
	 */
	public function error_message( $message )
	{
		return
			"<div class='BssError' >\n"
			. " <p style='line-height: 8px;'><strong>" . $message . "</strong></p>\n"
			. '</div>';
	}

	/**
	 * Return a formatted an info message.
	 *
	 * @since    1.0
	 * @param    string    $message    Message text.
	 * @return   string                Formated info message.
	 * @return   void
	 */
	public function info_message( $message )
	{
		return
			"<div class='BssInfo' >\n"
			. " <p style='line-height: 8px;'><strong>" . $message . "</strong></p>\n"
			. '</div>';
	}


	/**
	 * Return the column headers for a gridview.
	 *
	 * @since    1.0
	 * @params   string $sortStatus   Url parms to maintain state
	 * @params   array  $gvColumns    Array of columns
	 * @returns  string               Html segment containing a gridview header
	 */
	public function sort_headers( $sortStatus, $gvColumns )
	{
		$headerText = '';
		foreach ( $gvColumns as $gvColumn ) {
			if ( $gvColumn->Title == '' ) {
				$headerText .= ObjtrackerEasyStatic::table_th( '', '&nbsp;' );
			} elseif ( $gvColumn->DbColumnName == '' ) {
				$headerText .= ObjtrackerEasyStatic::table_th( '', $gvColumn->Title );
			} else {
				$dbColumnNumber = $gvColumn->DbColumnName == 'C_Track_Changed'
					? $this->Field->id_by_name( 'C_Track_SortedChanged' )
					: $this->Field->id_by_name( $gvColumn->DbColumnName );

				$headerText .= ObjtrackerEasyStatic::table_th(
						'',
						"<a href='" . $this->PathBase . $this->PlatformParm 
						. 'sc_menu=' . $this->MenuName . $this->PageState
						. '&sc_action=sort&fld=' . $dbColumnNumber . $sortStatus
						. "'>" . $gvColumn->Title . '</a>'
						);
			}
		}

		return
			ObjtrackerEasyStatic::table_tr_start( "class='BssGvHeader'" )
			. $headerText
			. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Add text to trace array.
	 *
	 * @since    1.0
	 * @param    string    $text    Trace data string.
	 * @return   void
	 */
	public function trace_text( $text )
	{
		if ( $this->Trace ) {
			array_push( $this->TraceArray, $text );
		}
	}

	/**
	 * Return a formated extract link.
	 *
	 * @since    1.0
	 * @param    string    $title    Name of link.
	 * @param    string    $parms    Page specific parms for link.
	 * @return   string              Formated link(s).
	 */
	function extract_link( $title, $parms )
	{
		$testlink = ( $this->Trace )
			? "<a href='" . $this->PathBase . $this->PlatformParm
				. 'sc_menu=ShowData&fname=' . $this->MenuName . '.tsv' . $parms
				. "&xmimetype=text/plain'>Test " . $title . "</a>\n"
			: '';
		return
			"<a href='" . $this->PathDownload
			. 'sc_menu=ShowData&fname=' . $this->MenuName . '.tsv' . $parms
			. "&mimetype=text/tab-separated-values'>" . $title . '</a>'
			. $testlink;
	}

	/**
	 * Remove dangerous characters from a filename.
	 *
	 * @since    1.0
	 * @param    string    $value    Input file name
	 * @return   string
	 */
	function cleanup_dbfilename( $value )
	{
		$special_chars = array( '?', '[', ']', '\\', '=', '<', '>', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', chr( 0 ) );
		return str_replace( $special_chars, '', $value );
	}

	/**
	 * For download of extracts or documents, clear buffers.
	 *
	 * @since    1.0
	 * @return   void
	 */
	function clear_buffers()
	{
		# installing at the toplevel
		$my_default_level = ob_get_level();	// learn about already set output buffers
		$my_has_buffer    = ob_start();		// my output buffer, with flagging

		// burning down (somewhere after)
		if ( $my_has_buffer ) {
			$c = ob_get_level() - $my_default_level;
			if ( $c > 0 ) {
				while ( $c-- ) {
					ob_end_clean();
				}
			}
		}
		//header( 'Content-Length: ' . filesize( $file ) );

	//	ob_clean();
		flush();
	}

	/**
	 * For download of extracts or documents, write http headers.
	 *
	 * @since    1.0
	 * @return   void
	 */
	function write_httpheaders()
	{
		ob_end_clean();
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: ' . $_GET['mimetype'] );
		header( 'Content-Disposition: attachment; filename=' . $_GET['fname'] );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Cache-control: max-age=0, no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
	}
}

/**
 * ObjtrackerDbField associates database query column names with a column number.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerDbField
{
	/**
	 * Constructor for ObjtrackerDbField
	 *
	 * @since    1.0
	 * @param    array    $varname    Array of columns
	 * @return   void
	 */
	function __construct( $columnArray )
	{
		for ( $i = 0; $i < count( $columnArray ); $i++ ) {
			$name = $columnArray[$i];
			$this->$name = $i;
		}
	}
	/**
	 * Get id of database column from its name.
	 *
	 * @since    1.0
	 * @param    string    $dbFieldName   Column name
	 * @return   int                      Column number
	 */
	public function id_by_name( $dbFieldName )
	{
		return ( property_exists( $this, $dbFieldName ) )
			? $this->$dbFieldName
			: 'badvalue';
	}

	/**
	 * Retrieve value of this object property
	 *
	 * @since    1.0
	 * @param    string    $property    Instance variable name
	 * @return   any                    Instance variable value
	 */
	public function __get( $property )
	{
		return ( property_exists( $this, $property ) )
			? $this->$property
			: 'badvalue';
	}
}

/**
 * Captures user input on a form in case it needs to be redisplayed.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerFormField
{
	/**
	 * Set value of instance variable
	 *
	 * @since    1.0
	 * @param    string    $name    Instance variable name
	 * @param    any       $value   Instance variable value
	 * @return   void
	 */
	function __set( $name, $value )
	{
		$this->$name = $value;
	}

	/**
	 * Retrieve instance variable value
	 *
	 * @since    1.0
	 * @param    string    $name    Instance variable name
	 * @return   any       $value   Instance variable value
	 */
	public function __get( $property )
	{
		return ( property_exists( $this, $property ) )
			? $this->$property
			: 'badvalue';
	}
}

?>
