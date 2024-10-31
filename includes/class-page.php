<?php
/**
 * Base class for all text between the menu and the trailer.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */
/**
 * Class supports common functionality between menu and trailer.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerPage
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
	 * Holds display title for page.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $PageTitle;

	/**
	 * Indicates action to be taken on page, see BSPAGE_* constants.
	 *
	 * @var char
	 * @access private
	 * @since 1.0
	 */
	protected $ActionChar;
	const BSPAGE_ADD   = 'a';	// Press ADD
	const BSPAGE_EDIT  = 'e';	// Press EDIT
	const BSPAGE_UPD   = 'u';	// Press UPDATE
	const BSPAGE_DEL   = 'd';	// Press DELETE
	const BSPAGE_NEW   = 'n';	// Press NEW
	const BSPAGE_LIST  = 'l';	// Just list
	const BSPAGE_CHA   = 'c';	// change
	const BSPAGE_SORT  = 's';	// sort
	const BSPAGE_RESET = 'r';	// reset

	/**
	 * Contains '', an info message, or an error message.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $ValidationMsg;

	/**
	 * True/false indicating a displayable error.
	 *
	 * @var bool
	 * @access private
	 * @since 1.0
	 */
	protected $UserError;

	/**
	 * Contains " style='text-align:right;' ".
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $StyleAlignRight;

	/**
	 * Holds array of descriptive text later display.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	protected $_Description;

	/**
	 * Holds the first fiscal year value in the generated fiscal year dropdown.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	protected $FiscalYear1;

	/**
	 * Holds the first fiscal year2 value in the generated fiscal year 2 dropdown.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	protected $FiscalYear2;

	/**
	 * Constructor for ObjtrackerPage.
	 *
	 * @since    1.0
	 * @params   $bsdriver           BsDriver object
	 * @params   $bsuser             BsUser object
	 * @params   $pageTitle          Title of the page.
	 * @returns  void
	 */
	function __construct( $bsdriver, $bsuser, $pageTitle )
	{
		$this->ValidationMsg   = '';
		$this->UserError       = false;
		$this->StyleAlignRight = " style='text-align:right;' ";

		$this->bsdriver = $bsdriver;
		$this->bsuser   = $bsuser;

		$this->PageTitle = $pageTitle;
	}
	/**
	 * Set name for show/hide description.
	 *
	 * @since    1.0
	 * @param    string    $title     Title of show/hide section
	 * @param    string    $settings  Show/hide set
	 * @param    string    $parm      Parameters from keeping page state
	 * @return   void
	 */
	protected function setpage_description_head( $title, $settings, $parm = '' )
	{
		$bsdriver = $this->bsdriver;

		$this->_Description = $bsdriver->set_description_head( $title, $settings, $parm );
	}

	/**
	 * Concatonate more description text.
	 *
	 * @since    1.0
	 * @param    string    $txt    Text of description.
	 * @return   void
	 */
	protected function description_text( $txt )
	{
		$bsdriver = $this->bsdriver;
		$this->_Description .= $bsdriver->description_text( $txt );
	}

	/**
	 * Description start of UL.
	 *
	 * @since    1.0
	 * @return   void
	 */
	protected function description_list_start()
	{
		$bsdriver = $this->bsdriver;
		$this->_Description .= $bsdriver->description_list_start();
	}

	/**
	 * Concatonate another UL item.
	 *
	 * @since    1.0
	 * @param    type    $txt    Bullet point text.
	 * @return   void
	 */
	protected function description_list_item( $txt )
	{
		$bsdriver = $this->bsdriver;
		$this->_Description .= $bsdriver->description_list_item( $txt );
	}

	/**
	 * End description UL.
	 *
	 * @since    1.0
	 * @return   void
	 */
	protected function description_list_end()
	{
		$bsdriver = $this->bsdriver;
		$this->_Description .= $bsdriver->description_list_end();
	}

	/**
	 * End description block.
	 *
	 * Long Description
	 *
	 * @since    1.0
	 * @return   void
	 */
	protected function description_end()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->_Description .= $bsdriver->description_tail();

	}

	/**
	 * Validate a stored procedure parameter.
	 *
	 * Long Description
	 *
	 * @since    1.0
	 * @param    string    $name       Name of string for reporting error.
	 * @param    string    $value      Value of string.
	 * @return   void.
	 */
	protected function is_valid_dbinteger( $name, $value )
	{
		$bsdriver = $this->bsdriver;

		$len = strlen( $value );
		if ( $len == 0 ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Field:' ) . ' ' . $name . ' ' . __( 'is required.' ) );
		} elseif ( !is_numeric( $value ) ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Field:' ) . ' ' . $name . ' ' . __( 'is not numeric.' ) );
		} elseif ( $value / 1 != $value ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Field:' ) . ' ' . $name . ' ' . __( 'is not numeric.' ) );
		}
		return !$this->UserError;
	}

	/**
	 * Validate a stored procedure parameter.
	 *
	 * Long Description
	 *
	 * @since    1.0
	 * @param    string    $name       Name of string for reporting error.
	 * @param    string    $value      Value of string.
	 * @return   void.
	 */
	protected function is_valid_dbchar( $name, $value )
	{
		$bsdriver = $this->bsdriver;

		if ( 1 != strlen( $value ) ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Field:' ) . ' ' . $name . ' ' . __( 'is required as one character.' ) );
		} elseif ( $value >= 'A' && $value <= 'Z' ) {
		} elseif ( $value >= '0' && $value <= '9' ) {
		} elseif ( $value == '$' ) {
		} elseif ( $value / 1 != $value ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Field:' ) . ' ' . $name . ' ' . __( 'is not one of A to Z, 0 to 9, or \$.' ) );
		}
		return !$this->UserError;
	}
	/**
	 * Validate a stored procedure parameter.
	 *
	 * Long Description
	 *
	 * @since    1.0
	 * @param    string    $value      Value of string.
	 * @return   void.
	 */
	protected function is_valid_dbpath( $value )
	{
		$bsdriver = $this->bsdriver;

		$len = strlen( $value );
		if ( $len == 0 ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Field: Upload path is required.' ) );
			return !$this->UserError;
		} else {
			$path = $bsdriver->cleanup_dbfilename( $value );
			$bsdriver->trace_text( ' value=' . $value . ',path=' . $path );
			if ( !is_writable( dirname( $path . '/testifwritable.stuff' ) ) ) {
				$this->UserError     = true;
				$this->ValidationMsg = $bsdriver->error_message( __( 'Upload path not writable' ) );
			}
		}
		return !$this->UserError;
	}

	/**
	 * Validate a stored procedure parameter.
	 *
	 * Long Description
	 *
	 * @since    1.0
	 * @param    int       $maxSize    Maximum length of string.
	 * @param    string    $name       Name of string for reporting error.
	 * @param    string    $value      Value of string.
	 * @param    string    $required   Required=true else required=false
	 * @return   void.
	 */
	protected function is_valid_dbparm( $maxSize, $name, $value, $required = true )
	{
		$bsdriver = $this->bsdriver;

		$len = strlen( $value );
		if ( $required && $len == 0 ) {
			$bsdriver->trace_text( ' ' . $name . ' ' . __( 'empty' ) );
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Field:' ) . ' ' . $name . ' ' . __( 'is required.' ) );
		} elseif ( $len > $maxSize ) {
			$bsdriver->trace_text( ' ' . $name . ' ' . __( 'toolong' ) );
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Field:' ) . ' ' . $name . ' ' . __( 'is longer than' ) . ' ' . $maxSize . ' ' . __( 'characters.' ) );
		} else {
			$special_chars = array( '?', '[', ']', '\\', '=', '<', '>', ';', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', chr( 0 ) );
			if ( str_replace( $special_chars, '', $value ) != $value ) {
				$this->UserError     = true;
				$this->ValidationMsg = $bsdriver->error_message( __( 'Field:' ) . ' ' . $name . ' ' . __( 'had invalid characters such as ?[]\\= <>;&$#*()|~, etc..' ) );
			} else {
				$bsdriver->trace_text( ' ' . $name . ' ' . __( 'valid' ) );
			}
		}
		return !$this->UserError;
	}

	/**
	 * Call stored procedure
	 *
	 * @since    1.0
	 * @param    string    $storedProcedure    Name of the stored procedure
	 * @param    string    sprintf format      (%d, %d, ...).
	 * @param    array     $parameters         Array of parameters for stored procedure.
	 * @param    string    $okayMessage        Info message if successfull.
	 * @return   void
	 */
	function db_change( $storedProcedure, $args, $parameters, $okayMessage, $db_messages )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$results = $bsdriver->platform_db_query( $storedProcedure, $args, $parameters );
		if ( count( $results ) > 0 ) {
			if ( isset( $results[0][$bsdriver->Field->C_ErrorID] ) ) {
		//		$bsdriver->trace_text( ' dberror set' );
				if ( $results[0][$bsdriver->Field->C_ErrorID] == '' ) {
					if ( $okayMessage == '' ) {
						$bsdriver->trace_text( ' dberror qqqq' );
					} else {
						$this->ValidationMsg = $bsdriver->info_message( $okayMessage );
						$bsdriver->trace_text( ' dberrorok=' . $this->ValidationMsg );
					}
					$this->ActionChar = self::BSPAGE_LIST;
					$this->UserError  = false;
				} else {
					$this->UserError     = true;
					$this->ValidationMsg = $bsdriver->error_message( $db_messages[$results[0][$bsdriver->Field->C_ErrorID]] );
					$bsdriver->trace_text( ' dberror=' . $this->ValidationMsg );
				}
			} else {
					$this->UserError     = true;
					$this->ValidationMsg = __( 'Internal error: C_ErrorID not set by' ) . ' ' . $storedProcedure;
					$bsdriver->trace_text( ' dberror=' . $this->ValidationMsg );
			}
		} else {
			$this->UserError = true;
//			$this->ValidationMsg = $storedProcedure . ' returned 0 rows';
		}
	}

	/**
	 * Make a public/private dropdown box
	 *
	 * @since    1.0
	 * @param    string    $selectedvalue    The value to be highlighted or '' for the first.
	 * @return   string                      Html fragment.
	 */
	function get_public_dropdown( $selectedvalue )
	{
		$bsdriver = $this->bsdriver;

		if ( $selectedvalue == '' )
			$selectedvalue = 'Public';

		$ddl = "<select name='C_IsPublic' id='C_IsPublic' >\n";

		$ddl .= " <option value='Yes'";
		$ddl .= $selectedvalue == 'Yes'
					? " selected='selected'>Public</option><option value='No' >Private</option>\n"
					: " >Public</option><option value='No' selected='selected' >Private</option>\n";
		$ddl .= "</select>\n";
		return $ddl;
	}

	/**
	 * Make a objective type dropdown box
	 *
	 * @since    1.0
	 * @param    string    $selectedvalue    The value to be highlighted or '' for the first.
	 * @return   string                      Html fragment.
	 */
	function get_objecttype_dropdown( $selectedvalue )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$results = $bsdriver->platform_db_query(
			'P_ObjectiveTypeList',
			'( %d, %d )',
			array( $bsuser->OrgID, $bsuser->ID )
			);
		if ( $selectedvalue == '' )
			$selectedvalue = $results[0][0];

		$ddl = "<select name='C_ObjectiveTypeID' id='C_ObjectiveTypeID' >\n";

		foreach ( $results as $row ) {
			if ( $selectedvalue == $row[$bsdriver->Field->C_ID])
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "' selected='selected'>"
						. $row[$bsdriver->Field->C_Title] . " </option>\n";
			else
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "'>" . $row[$bsdriver->Field->C_Title] . " </option>\n";
		}
		$ddl .= "</select>\n";
		return $ddl;
	}

	/**
	 * Make a metric type dropdown box
	 *
	 * @since    1.0
	 * @param    string    $selectedvalue    The value to be highlighted or '' for the first.
	 * @return   string                      Html fragment.
	 */
	function get_metrictype_dropdown( $selectedvalue )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$results = $bsdriver->platform_db_query(
			'P_MetricTypeList',
			'( %d, %d )',
			array( $bsuser->OrgID, $bsuser->ID )
			);
		if ( $selectedvalue == '' )
			$selectedvalue = $results[0][0];

		$ddl = "<select name='C_MetricTypeID' id='C_MetricTypeID' >\n";

		foreach ( $results as $row ) {
			$txt = $row[$bsdriver->Field->C_Title] . ' ( ' . $row[$bsdriver->Field->C_Description] . ' )';
			if ( $selectedvalue == $row[$bsdriver->Field->C_ID])
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "' selected='selected'>" . $txt . " </option>\n";
			else
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "'>" . $txt . " </option>\n";
		}
		$ddl .= "</select>\n";
		return $ddl;
	}

	/**
	 * Make a frequency dropdown box
	 *
	 * @since    1.0
	 * @param    string    $selectedvalue    The value to be highlighted or '' for the first.
	 * @return   string                      Html fragment.
	 */
	function get_frequency_dropdown( $selectedvalue )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$results = $bsdriver->platform_db_query(
			'P_FrequencyList',
			'( %d, %d )',
			array( $bsuser->OrgID, $bsuser->ID )
			);
		if ( $selectedvalue == '' )
			$selectedvalue = $results[0][0];

		$ddl = "<select name='C_FrequencyID' id='C_FrequencyID' >\n";

		foreach ( $results as $row ) {
			if ( $selectedvalue == $row[$bsdriver->Field->C_ID])
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "' selected='selected'>"
						. $row[$bsdriver->Field->C_Title] . " </option>\n";
			else
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "'>" . $row[$bsdriver->Field->C_Title] . " </option>\n";
		}
		$ddl .= "</select>\n";
		return $ddl;
	}

	/**
	 * Make an owner dropdown box
	 *
	 * @since    1.0
	 * @param    string    $selectedvalue    The value to be highlighted or '' for the first.
	 * @param    string    $activeonly       'ActiveOnly' to restrict dropdown to only active people.
	 * @return   string                      Html fragment.
	 */
	function get_owner_dropdown( $selectedvalue, $activeonly )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $activeonly == 'ActiveOnly' ) {
			$results = $bsdriver->platform_db_query(
				'P_PersonList',
				'( %d, %d, %s )',
				array( $bsuser->OrgID, $bsuser->ID, 'False' )
			);
		} else {
			$results = $bsdriver->platform_db_query(
				'P_PersonList',
				'( %d, %d, %s )',
				array( $bsuser->OrgID, $bsuser->ID, 'True' )
			);
		}
		if ( $selectedvalue == '' )
			$selectedvalue = $results[0][0];

		$ddl = "<select name='C_OwnerID' id='C_OwnerID' >\n";

		foreach ( $results as $row ) {
			if ( $selectedvalue == $row[$bsdriver->Field->C_ID])
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "' selected='selected'>"
					. $row[$bsdriver->Field->C_FullName] . " </option>\n";
			else
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "'>" 
					. $row[$bsdriver->Field->C_FullName] . " </option>\n";
		}
		$ddl .= "</select>\n";
		return $ddl;
	}

	/**
	 * Make a fiscal year dropdown box
	 *
	 * @since    1.0
	 * @param    string    $selectedvalue    The value to be highlighted or '' for the first.
	 * @return   string                      Html fragment.
	 */
	function get_fiscalyear_dropdown( $field, $selectedvalue, $onchange )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$results = $bsdriver->platform_db_query(
			'P_FiscalYearList',
			'( %d, %d )',
			array( $bsuser->OrgID, $bsuser->ID )
		);
		if ( count( $results ) == 0 ) {
			return __( 'No fiscal years defined.' );
		} elseif ( $selectedvalue == '' ) {
			$selectedvalue = $results[0][0];
			foreach ( $results as $r ) {
				if ( $bsuser->FiscalYear1 == $r[$bsdriver->Field->C_ID] ) {
					$selectedvalue = $bsuser->FiscalYear1;
				}
			}
		}

		$ddl = "<select name='" . $field . "' id='" . $field . "' " . $onchange . " >\n";

		foreach ( $results as $row ) {
			if ( $selectedvalue == $row[$bsdriver->Field->C_ID] ) {
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "' selected='selected'>"
						. $row[$bsdriver->Field->C_Title] . " </option>\n";
				$this->FiscalYear1 = $row[$bsdriver->Field->C_ID];
			} else
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "'>" . $row[$bsdriver->Field->C_Title] . " </option>\n";
		}
		$ddl .= "</select>\n";
		return $ddl;
	}


	/**
	 * Make a fiscal year 2 dropdown box
	 *
	 * @since    1.0
	 * @param    string    $selectedvalue    The value to be highlighted or '' for the first.
	 * @return   string                      Html fragment.
	 */
	function get_fiscalyear2_dropdown( $field, $selectedvalue, $onchange )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$results = $bsdriver->platform_db_query(
			'P_FiscalYear2List',
			'( %d, %d )',
			array( $bsuser->OrgID, $bsuser->ID )
			);
		if ( count( $results ) == 0 )
			return '';
		if ( $selectedvalue == '' ) {
			$selectedvalue = $results[0][0];
			foreach ( $results as $r ) {
				if ( $bsuser->FiscalYear1 == $r[$bsdriver->Field->C_ID] ) {
					$selectedvalue = $bsuser->FiscalYear1;
				}
			}
		}

		$ddl = "<select name='" . $field . "' id='" . $field . "' " . $onchange . " >\n";

		foreach ( $results as $row ) {
			if ( $selectedvalue == $row[$bsdriver->Field->C_ID] ) {
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "' selected='selected'>"
						. $row[$bsdriver->Field->C_Title] . " </option>\n";
				$this->FiscalYear2 = $row[$bsdriver->Field->C_ID];
			} else
				$ddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "'>" . $row[$bsdriver->Field->C_Title] . " </option>\n";
		}
		$ddl .= "</select>\n";
		return $ddl;
	}
}
/**
 * Class supports basic function between menu and trailers.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerConfigPage extends ObjtrackerPage
{
	/**
	 * Holds array of columns for the grid view.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	protected $GvColumns;

	/**
	 * Holds name of display stored procedure.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $DbProcList;

	/**
	 * The string including parens that follows CALL ...procurename.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	protected $DbProcArgs;

	/**
	 * Holds array of parameters for stored procedure.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	protected $DbProcParms;


	/**
	 * Holds rows return count from database query.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	protected $RowCount;

	/**
	 * Holds last sort column for grid view. 
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	protected $HiddenSortField;

	/**
	 * Holds last sort direction for grid view. 
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $HiddenSortDirection;

	/**
	 * Holds new sort direction for grid view. 
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $SortDirection;

	/**
	 * Holds string to be displayed on a successful delete.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $OnDeleteMsg;

	/**
	 * Holds array database results.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	protected $GvResults;

	/**
	 * Constructor for ObjtrackerConfigPage.
	 *
	 * @since    1.0
	 * @params   $bsdriver           BsDriver object
	 * @params   $bsuser             BsUser object
	 * @params   $pageTitle          Page title
	 * @params   $dbProcList         Name of stored procedure for listing page items
	 * @params   $dbProcArgs         Array of arguements to stored procedure
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
		$this->SortDirection = '';

		$this->bsdriver    = $bsdriver;
		$this->bsuser      = $bsuser;
		$this->PageTitle   = $pageTitle;
		$this->GvColumns   = $arrayOfGvColumns;
		$this->DbProcList  = $dbProcList;
		$this->DbProcArgs  = $dbProcArgs;
		$this->DbProcParms = $dbProcParms;
		$this->OnDeleteMsg = $onDeleteMsg;
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

		// Setup
		if ( isset( $_GET[ 'SortHiddenField' ] ) ) {
			$this->HiddenSortField     = $_GET['SortHiddenField'];
			$this->HiddenSortDirection = $_GET['SortHiddenDirection'];
//			$bsdriver->trace_text( 'get hidden Sortfield ' . $this->HiddenSortDirection . ' ' . $this->HiddenSortField );
		} else {
			$this->HiddenSortField     = '' ;
			$this->HiddenSortDirection = '';
		}
//		$bsdriver->trace_text( 'hidden(' . $this->HiddenSortDirection . $this->HiddenSortField . ')' );

		$message          = '';
		$this->ActionChar = substr( $bsdriver->Action, 0, 1 );
//		$bsdriver->trace_text( ' action(' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		switch ( $this->ActionChar ) {
			case self::BSPAGE_ADD: // User press "Add" button
				$this->row_insert();
				break;
			case self::BSPAGE_EDIT: // User press "Edit" button
				break;
			case self::BSPAGE_UPD: // User press "Update" button
				$this->row_update();
				break;
			case self::BSPAGE_DEL: // User press "Delete" button
				$this->row_delete();
				break;
			case self::BSPAGE_SORT: // User click to sort by column
				$lastSort = $_GET[ 'Ss' ];
				if ( strlen( $lastSort ) > 1 ) {
					$lastField     = substr( $lastSort, 2 );
					$lastDirection = substr( $lastSort, 0, 1 );

					if ( $lastField == $bsdriver->SortField ) {
						$bsdriver->trace_text( ' sort same' );
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
			case self::BSPAGE_RESET: // User press "other" button
				$this->row_reset();		// Peoples passwords
				break;
			case self::BSPAGE_CHA: // User press "other" button
				$this->other_button();	// Default password
				break;
			default:	// Initial or no action
				break;
		}
		$prefix = $this->preface();
		$this->description();
		$prefix2 = $this->preface2();

		$this->GvResults = $bsdriver->platform_db_query(
				$this->DbProcList,
				$this->DbProcArgs,
				$this->DbProcParms
			);
		$this->RowCount  = count( $this->GvResults );

		return
			"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
			. $bsdriver->platform_start_form( '', '' )
			. $prefix
			. $this->_Description
			. $prefix2
			. $this->ValidationMsg
			. $this->gridview1()
			. $this->trailer()
			. $bsdriver->EndForm;
	}

	/**
	 * Make an additional button for this page.
	 *
	 * @since    1.0
	 * @return   string                      Html fragment.
	 */
	public function other_button()
	{
	}

	/**
	 * When a db query completes, call me.
	 *
	 * @since    1.0
	 * @param    string    $selectedvalue    The value to be highlighted or '' for the first.
	 * @return   string                      Html fragment.
	 */
	public function on_db_results()
	{
	}

	/**
	 * Return text after description
	 *
	 * @since    1.0
	 * @param    string    $selectedvalue    The value to be highlighted or '' for the first.
	 * @return   string                      Html fragment.
	 */
	public function preface()
	{
		return '';
	}

	/**
	 * Setup controls/text after the description.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	public function preface2()
	{
		return '';
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
	//		$bsdriver->trace_text( 'No sorting, HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );
			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='' />\n"
				. "<input type='hidden' name='SortHiddenDirection' value='' />\n";
			$sortUrl      = '&Ss=';
		}

		// Write each row of gridview
		$tableBody  = '';
		$primaryRow = true;
		foreach ( $this->GvResults as $row ) {
			$tableBody .= $primaryRow ? "<tr class='BssGvOddRow'>\n" : "<tr class='BssGvEvenRow'>\n";
			$primaryRow = $primaryRow ? false : true;
			$C_ID = $row[$bsdriver->Field->C_ID];

			if ( $this->ActionChar == self::BSPAGE_EDIT && $C_ID == $_GET['id'] ) {
				$bsdriver->trace_text( ' gridview_row_edit_this' );
				$tableBody .= $this->gridview_row_edit_this( $row );
			} elseif ( $this->ActionChar == self::BSPAGE_UPD && $C_ID == $_POST['C_ID'] ) {
					$tableBody .= $this->gridview_row_update_this( $row );
			} elseif ( $this->ActionChar == self::BSPAGE_EDIT || $this->ActionChar == self::BSPAGE_UPD ) {
				$tableBody .= $this->gridview_row_other( $row );
			} else {
				$tableBody .= $this->gridview_row_list( $row );
			}
			$tableBody .= "</tr> <!-- close Scorecard-Gridview-Row --> \n";
		}

		if ( $this->ActionChar != self::BSPAGE_EDIT && $this->ActionChar != self::BSPAGE_UPD ) {
			if ( $this->UserError && $this->ActionChar == self::BSPAGE_ADD )
				$footer = $this->gridview_footer_error();
			else
				$footer = $this->gridview_footer();
		} else
			$footer = '';

		return	$hiddenValues
				. ObjtrackerEasyStatic::table_start( "class='BssGridview'" )
				. $bsdriver->sort_headers( $sortUrl, $this->GvColumns )
				. $tableBody
				. $footer
				. ObjtrackerEasyStatic::table_end();
	}
}
?>
