<?php
/**
 * Presents lists of objectives and manages the adding of an objective.
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
function bs_objectives( $bsdriver, $bsuser )
{

	if ( isset( $_GET['DeptDDL'] ) ) {
		$deptddl = $_GET['DeptDDL'];
		$bsdriver->trace_text( ' get C_DeptDDL=' . $deptddl );
	} elseif ( $bsuser->is_admin() ) {
		$deptddl = '0-99999';
		$bsdriver->trace_text( ' nogetAdmin=' . $deptddl );
	} else {
		$deptddl = 'U' . $bsuser->ID;
		$bsdriver->trace_text( ' nogetLoser=' . $deptddl );
	}

	if ( isset( $_GET['TimeDDL'] ) ) {
		$timeddl = $_GET['TimeDDL'];
		$bsdriver->trace_text( ' get C_TimeDDL=' . $timeddl );
	} else {
		$timeddl = 'Current';
		$bsdriver->trace_text( ' noget=' . $timeddl );
	}

	$configPage = new BsObjectivesPage(
		$bsdriver,
		$bsuser,
		__( "Table's Columns" ),		// Parm: Title
		array(
				new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
				new ObjtrackerGvColumn( __( 'Measures' ), 'C_Usage' ),
				new ObjtrackerGvColumn( __( 'Dept' ), 'C_DeptTitle2' ),
				new ObjtrackerGvColumn( __( 'Objective' ), 'C_Title' ),
				new ObjtrackerGvColumn( __( 'Frequency' ), 'C_Frequency' ),
				new ObjtrackerGvColumn( __( 'Public' ), 'C_IsPublic' ),
				new ObjtrackerGvColumn( __( 'Fiscal Years' ), 'C_FY1Title' ),
				new ObjtrackerGvColumn( '', '' ),
			),
		'P_ObjectiveList',
		'( %d, %d, %s, %s)',
		array( $bsuser->OrgID, $bsuser->ID, $deptddl, $timeddl ),
		'Are you sure you want to delete this objective?'
		);
	return $configPage->Response();
}

/**
 * Objectives processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsObjectivesPage extends ObjtrackerConfigPage
{
	const PARM_DEPT = 2;
	const PARM_TIME = 3;

	/**
	 * Count of fiscal years.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	private $fiscalyear_count;

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
			'ObjDelHas'	=> __( 'Objective has measurements, can not delete!' ),
			'OrgInsTitle'	=> __( 'Title field is required' ),
			'OrgInsPath'	=> __( 'Upload path is required' ),
			'OrgInsPath'	=> __( 'Title already exists' ),
			);
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

		$this->have_fiscalyears();
		if ( $this->fiscalyear_count == 0 ) {
			$this->ValidationMsg = $bsdriver->error_message( __( 'Before using this page, fiscal years must be defined.' ) );

			$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
			$this->description_text(
				__(
					'This page lists the objectives assigned to individual departments for the current and/or all fiscal years.'
					)
				);
		$this->description_text(
				__(
					'First, go to the Admin Organization page and insure that the first month is set correctly,
					then go to Admin Fiscal Years to add at least one fiscal year.'
					)
				);
			$this->description_end();
			return 	$this->_Description . $bsdriver->title_heading( __( 'Objectives' ) ) . $this->ValidationMsg ;
		}

		// Setup
		if ( isset( $_GET[ 'SortHiddenField' ] ) ) {
			$this->HiddenSortField     = $_GET['SortHiddenField'];
			$this->HiddenSortDirection = $_GET['SortHiddenDirection'];
			$bsdriver->trace_text( 'get hidden Sortfield ' . $this->HiddenSortDirection . ' ' . $this->HiddenSortField );
		} else {
			$this->HiddenSortField     = '' ;
			$this->HiddenSortDirection = '';
		}
		$bsdriver->trace_text( 'hidden(' . $this->HiddenSortDirection . $this->HiddenSortField . ')' );

		$message          = '';
		$this->ActionChar = substr( $bsdriver->Action, 0, 1 );
		$bsdriver->trace_text( ' action(' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		switch ( $this->ActionChar ) {
			case self::BSPAGE_ADD: // User press "Add" button
				if ( $bsuser->is_admin() ) {
					$this->row_insert();
					if ( $this->UserError )
						return $this->row_new();
				}
				$this->ActionChar = self::BSPAGE_LIST;
				break;
			case self::BSPAGE_EDIT: // User press "Edit" button
				break;
			case self::BSPAGE_UPD: // User press "Update" button
				break;
			case self::BSPAGE_DEL: // User press "Delete" button
				$this->row_delete();
				break;
			case self::BSPAGE_NEW: // User press "New" button
				if ( $bsuser->is_admin() )
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
				$this->row_reset();	// People's passwords
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

		return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $prefix
				. $this->_Description
				. $bsdriver->platform_start_form( '', '' )
				. $prefix2
				. $bsdriver->EndForm
				. $this->ValidationMsg
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

		$bsdriver->PageState = '&DeptDDL=' . $this->DbProcParms[self::PARM_DEPT] . '&TimeDDL=' . $this->DbProcParms[self::PARM_TIME];

		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text(
			__(
				'The table below lists the objectives assigned to individual departments for the
				current and/or all fiscal years.'
				)
			);

		$this->description_list_start();

		$this->description_list_item(
			__(
				'To sort the table, click on <b>ID</b>, <b>People</b>, <b>Title</b>, <b>Short Title</b>,
				<b>Active</b>, <b>Changed</b>, or <b>By</b>.'
				)
			);
		if ( $bsuser->is_admin() ) {
			$this->description_list_item(
				__(
					'To change selected objectives, select from <b>Department</b> and from <b>Fiscal Year</b>.'
					)
				);
			$this->description_list_item( __( 'To delete an unreferenced department, click on <b>Delete</b>.' ) );
			$this->description_list_item(
				__(
					'To create a new objective, click on <b>New</b>, specify appropriate details, and
					then click on <b>Save</b>.'
					)
				);
			$this->description_list_item(
				__(
					'To view a formatted report of the selected objectives, click on <b>Report</b>.'
					)
				);
			$this->description_list_item(
				__(
					'To extract a spreadsheet of the selected objectives, click on <b>Spreadsheet Download</b>.'
				)
			);
		}
		$this->description_list_end();
		$this->description_end();
	}

	/**
	 * Explain now to use new form by updating class variable.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function new_description()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$bsdriver->PageState = '&sc_action=new&DeptDDL=' . $this->DbProcParms[self::PARM_DEPT] . '&TimeDDL=' . $this->DbProcParms[self::PARM_TIME];
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

		$selectedDepartment = $this->DbProcParms[self::PARM_DEPT];

		$results = $bsdriver->platform_db_query(
			'P_DepartmentList',
			'( %d, %d, %s, %s, %d )',
			array( $bsuser->OrgID, $bsuser->ID, 'Ignored', 'True', $bsuser->ID )
			);

		$onChange = "BsOnChange('" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Objectives&DeptDDL=' . "',this.form.C_DeptDDL,'&TimeDDL=',this.form.C_TimeDDL)";

		$deptddl = "&nbsp;&nbsp;&nbsp;<select name='C_DeptDDL' id='C_DeptDDL' onchange=\""
			. $onChange . "\" >\n";

		foreach ( $results as $row ) {
			if ( $row[$bsdriver->Field->C_IsActive] == 'Yes' ) {
				$inactive_prefix = '';
			} else {
				$inactive_prefix = __( '(inactive) ' );
			}

			if ( $selectedDepartment == $row[$bsdriver->Field->C_ID] )
				$deptddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "' selected='selected'>"
					. $inactive_prefix . $row[$bsdriver->Field->C_Title] . " </option>\n";
			else
				$deptddl .= " <option value='" . $row[$bsdriver->Field->C_ID] . "'>"
					. $inactive_prefix . $row[$bsdriver->Field->C_Title] . " </option>\n";
		}
		$deptddl .= "</select>\n";

		$selectedTime = $this->DbProcParms[self::PARM_TIME];
		$timeddl      = "<select name='C_TimeDDL' id='C_TimeDDL' onchange=\"" . $onChange . "\" >\n";

		$timeddl .=	$selectedTime == 'Current'
					? " <option value='Current' selected='selected'>" . __( 'This FY' ) . "</option><option value='All' >" . __( 'All FY' ) . "</option>\n"
					: " <option value='Current'>" . __( 'This FY' ) . "</option><option value='All' selected='selected' >" . __( 'All FY' ) . "</option>\n";
		$timeddl .= "</select>\n";

		$new = ( $bsuser->is_admin() )
				? "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . "sc_menu=Objectives&sc_action=new'>New</a>"
				: '';

		$this->GvResults = $bsdriver->platform_db_query( $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );
		$this->RowCount  = count( $this->GvResults );

		$report = $this->RowCount == 0
				? ''
				: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm
				. 'sc_menu=ReportObjectives&NoMenu=Y&Dept=' . $selectedDepartment
				. '&Time=' . $selectedTime . "'>Report</a>";
		$bsdriver->trace_text( ' RowCount=' . $this->RowCount );

		return
			$bsdriver->title_heading( 'Objectives' )
			. '<b>' . __( 'Department:' ) . '</b> ' . $deptddl
			. '&nbsp;&nbsp;&nbsp;' . $timeddl
			. '&nbsp;&nbsp;&nbsp;' . __( 'Rows returned:' ) . ' ' . $this->RowCount
			. '&nbsp;&nbsp;&nbsp;' . $new
			. '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $report
			. "<br />\n";
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

		if ( $bsuser->is_admin() )
			return ( $this->RowCount == 0 )
				? '<br />&nbsp;&nbsp;&nbsp;' . __( 'No rows returned' ) . '<br />'
				: $bsdriver->extract_link(
					__( 'Spreadsheet Download' ),
					'&A=C_ID&V=' . $this->DbProcParms[self::PARM_DEPT] . '&A2=C_Time&V2=' . $this->DbProcParms[self::PARM_TIME]
					);
		else
			return ( $this->RowCount == 0 )
				? "<br />&nbsp;&nbsp;&nbsp;No rows returned<br />\n"
				: '';
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

		$this->UserInput                    = new ObjtrackerFormField();
		$this->UserInput->C_Title           = trim( $_POST['C_Title'] );
		$this->UserInput->C_IsPublic        = $_POST['C_IsPublic'];
		$this->UserInput->C_ObjectiveTypeID = $_POST['C_ObjectiveTypeID'];
		$this->UserInput->C_MetricTypeID    = $_POST['C_MetricTypeID'];
		$this->UserInput->C_FrequencyID     = $_POST['C_FrequencyID'];
		$this->UserInput->C_FiscalYear1     = $_POST['C_FiscalYear1'];
		$this->UserInput->C_FiscalYear2     = $_POST['C_FiscalYear2'];
		$this->UserInput->C_Description     = trim( $_POST['C_Description'] );
		$this->UserInput->C_Source          = trim( $_POST['C_Source'] );
		$this->UserInput->C_OwnerID         = $_POST['C_OwnerID'];

		$validator = new ObjtrackerValidateTargets(
						$this->UserInput->C_MetricTypeID,
						trim( $_POST['C_Target'] ),
						trim( $_POST['C_Target1'] ),
						trim( $_POST['C_Target2'] )
						);
		// Retrieved normalized strings
		$this->UserInput->C_Target  = $validator->normalized_target();
		$this->UserInput->C_Target1 = $validator->normalized_target1();
		$this->UserInput->C_Target2 = $validator->normalized_target2();

		if ( !$this->is_valid_dbparm( 100, __( 'Title' ), $this->UserInput->C_Title ) ) {
		} elseif ( !$this->is_valid_dbinteger( 'FY1', $this->UserInput->C_FiscalYear1 ) ) {
		} elseif ( !$this->is_valid_dbinteger( 'FY2', $this->UserInput->C_FiscalYear2 ) ) {
		} elseif ( $this->UserInput->C_FiscalYear2 < $this->UserInput->C_FiscalYear1 ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Fiscal year 1 must be less than or equal to fiscal year 2.' ) );
		} elseif ( !$this->is_valid_dbparm( 1024, __( 'Description' ), $this->UserInput->C_Description ) ) {
		} elseif ( !$this->is_valid_dbparm( 100, __( 'Source' ), $this->UserInput->C_Source, false ) ) {
		} elseif ( strlen( $validator->error() ) > 0 ) {
			$this->ValidationMsg = $bsdriver->error_message( $validator->error() );
			$this->UserError     = true;
		} else {
			$bsdriver->trace_text( ' <br>C_FiscalYear1=' . $this->UserInput->C_FiscalYear1 );
			$bsdriver->trace_text( ' <br>C_FiscalYear2=' . $this->UserInput->C_FiscalYear2 );
			$bsdriver->trace_text( ' <br>C_OwnerID=' . $this->UserInput->C_OwnerID );
			$bsdriver->trace_text( ' <br>C_Target1=' . $this->UserInput->C_Target1 );
			$bsdriver->trace_text( ' <br>C_Target2=' . $this->UserInput->C_Target2 );
			$bsdriver->trace_text( ' <br>C_IsPublic=' . $this->UserInput->C_IsPublic );
			$bsdriver->trace_text( ' <br>C_Source=' . $this->UserInput->C_Source );
			$bsdriver->trace_text( ' <br>C_ObjectiveTypeID=' . $this->UserInput->C_ObjectiveTypeID );
			$bsdriver->trace_text( ' <br>C_FrequencyID=' . $this->UserInput->C_FrequencyID );
			$bsdriver->trace_text( ' <br>C_MetricTypeID=' . $this->UserInput->C_MetricTypeID );
			$bsdriver->trace_text( ' <br>C_Target=' . $this->UserInput->C_Target );
			$bsdriver->trace_text( ' <br>C_Title=' . $this->UserInput->C_Title );
			$bsdriver->trace_text( ' <br>C_Description=' . $this->UserInput->C_Description );
			$this->db_change(
					'P_ObjectiveInsert',
					'( %d, %d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )',
					array(
						$bsuser->OrgID, $bsuser->ID,
						$this->UserInput->C_FiscalYear1,
						$this->UserInput->C_FiscalYear2,
						$this->UserInput->C_OwnerID,
						$this->UserInput->C_Target1,
						$this->UserInput->C_Target2,
						$this->UserInput->C_IsPublic,
						$this->UserInput->C_Source,
						$this->UserInput->C_ObjectiveTypeID,
						$this->UserInput->C_FrequencyID,
						$this->UserInput->C_MetricTypeID,
						$this->UserInput->C_Target,
						$this->UserInput->C_Title,
						$this->UserInput->C_Description,
						),
					'The objective has been added.',
				$this->db_messages
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

		return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $bsdriver->platform_start_form( '', '' )
				. "<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "'/> \n"
				. $this->_Description
				. $txt = $bsdriver->description_headertext( 'New Objective' )
				. $this->ValidationMsg
				. ObjtrackerEasyStatic::table_start( "cellspacing='0' id='BsFV1' class='BssFormview' style='width:700px;border-collapse:collapse'" )
				. $this->row_title()
				. $this->row_types()
				. $this->row_fiscalyears()
				. $this->row_description()
				. $this->row_source()
				. $this->row_owner()
				. $this->row_metrictype()
				. $this->row_target()
				. ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "'width:25%'", '&nbsp;' )
				. ObjtrackerEasyStatic::table_td( "'text-align: center'", $bsdriver->Input0SubmitAdd . $bsdriver->Input0SubmitCancel )
				. ObjtrackerEasyStatic::table_tr_end()
				. ObjtrackerEasyStatic::table_end()
				. $bsdriver->EndForm;
	}

	/**
	 * Return title row of form for adding an objective.
	 *
	 * @since    1.0
	 * @return   string                Fragment of html.
	 */
	function row_title()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$style = " maxlength='100'";
		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$cell = "<input type='text' name='C_Title' value='' " . $style . ' />';
		} else {
			$cell = "<input type='text' name='C_Title' value='" . $this->UserInput->C_Title . "' " . $style . ' />';
		}

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%'", '<b>' . __( 'Title' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $cell  )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return types row of form for adding an objective.
	 *
	 * @since    1.0
	 * @return   string                Fragment of html.
	 */
	function row_types()
	{
		$bsdriver = $this->bsdriver;

		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$C_IsPublic        = '';
			$C_ObjectiveTypeID = '';
			$C_FrequencyID     = '';
		} else {
			$C_IsPublic        = $this->UserInput->C_IsPublic;
			$C_ObjectiveTypeID = $this->UserInput->C_ObjectiveTypeID;
			$C_FrequencyID     = $this->UserInput->C_FrequencyID;
		}

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%'", '<b>' . __( 'Type' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td(
					'',
					$this->get_public_dropdown( $C_IsPublic ) . ':&nbsp;&nbsp;&nbsp;'
					. $this->get_objecttype_dropdown( $C_ObjectiveTypeID ) . ':&nbsp;&nbsp;&nbsp;'
					. $this->get_frequency_dropdown( $C_FrequencyID )
					)
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return metric types row of form for adding an objective.
	 *
	 * @since    1.0
	 * @return   string                Fragment of html.
	 */
	function row_metrictypes()
	{
		$bsdriver = $this->bsdriver;

		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$type = '';
		} else {
			$type = $this->UserInput->C_MetricTypeID;
		}

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%'", '<b>' . __( 'Type' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $this->get_metrictype_dropdown( $type ) )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return fiscal year row of form for adding an objective.
	 *
	 * @since    1.0
	 * @return   string                Fragment of html.
	 */
	function row_fiscalyears()
	{
		$bsdriver = $this->bsdriver;

		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$C_FiscalYear1 = '';
			$C_FiscalYear2 = '';
		} else {
			$C_FiscalYear1 = $this->UserInput->C_FiscalYear1;
			$C_FiscalYear2 = $this->UserInput->C_FiscalYear2;
		}

		return '<tr><td><b>FY Range</b></td><td>'
				. $this->get_fiscalyear_dropdown( 'C_FiscalYear1', $C_FiscalYear1, '' ) . ':&nbsp;&nbsp;&nbsp;'
				. $this->get_fiscalyear_dropdown( 'C_FiscalYear2', $C_FiscalYear2, '' ) . "</td></tr>\n";
		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%'", '<b>' . __( 'FY Range' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td(
					'',
					$this->get_fiscalyear_dropdown( 'C_FiscalYear1', $C_FiscalYear1, '' )
					. ':&nbsp;&nbsp;&nbsp;'
					. $this->get_fiscalyear_dropdown( 'C_FiscalYear2', $C_FiscalYear2, '' )
					)
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return description row of form for adding an objective.
	 *
	 * @since    1.0
	 * @return   string                Fragment of html.
	 */
	function row_description()
	{
		$bsdriver = $this->bsdriver;

		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$C_Description = '';
		} else {
			$C_Description = $this->UserInput->C_Description;
		}

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td(
					"style='width:25%'",
					'<b>' . __( 'Description' ) . '</b><br /><br />' . __( 'Use Shift+Enter for newline' )
					)
				. ObjtrackerEasyStatic::table_td(
						'',
					"<textarea name='C_Description' rows='6' cols='130' maxlength='1024'>"
					. $C_Description . '</textarea>'
					)
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return source row of form for adding an objective.
	 *
	 * @since    1.0
	 * @return   string                Fragment of html.
	 */
	function row_source()
	{
		$bsdriver = $this->bsdriver;

		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$C_Source = '';
		} else {
			$C_Source = $this->UserInput->C_Source;
		}

		return '<tr><td><b>Source</b></td><td>'
			. "<input type='text' name='C_Source' maxlength='100' "
			. " value='" . $C_Source . "'</td></tr>\n";
		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%'", '<b>' . __( 'Source' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td(
					'',
					"<input type='text' name='C_Source' maxlength='100' " . " value='" . $C_Source . "'"
					)
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return owner row of form for adding an objective.
	 *
	 * @since    1.0
	 * @return   string                Fragment of html.
	 */
	function row_owner()
	{
		$bsdriver = $this->bsdriver;

		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$C_OwnerID = '';
		} else {
			$C_OwnerID = $this->UserInput->C_OwnerID;
		}

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%'", '<b>' . __( 'Owner' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $this->get_owner_dropdown( $C_OwnerID, 'ActiveOnly' ) )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return metric type row of form for adding an objective.
	 *
	 * @since    1.0
	 * @return   string                Fragment of html.
	 */
	function row_metrictype()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$C_MetricTypeID = '';
		} else {
			$C_MetricTypeID = $this->UserInput->C_MetricTypeID;
		}

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%'", '<b>' . __( 'Metric Type' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $this->get_metrictype_dropdown( $C_MetricTypeID ) )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return target row of form for adding an objective.
	 *
	 * @since    1.0
	 * @return   string                Fragment of html.
	 */
	function row_target()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $this->ActionChar == self::BSPAGE_NEW ) {
			$C_Target  = '';
			$C_Target1 = '';
			$C_Target2 = '';
		} else {
			$C_Target  = $this->UserInput->C_Target;
			$C_Target1 = $this->UserInput->C_Target1;
			$C_Target2 = $this->UserInput->C_Target2;
		}

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='width:25%'", '<b>' . __( 'Target' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td(
					'',
					"<input type='text' name='C_Target' style='width:80px;' maxlength='12' value='"
					. $C_Target . "'/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Near(Green):</b>"
					. "<input type='text' name='C_Target1' style='width:80px;' maxlength='12' value='"
					. $C_Target1 . "'/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Far(Yellow):</b>"
					. "<input type='text' name='C_Target2' style='width:80px;' maxlength='12' value='"
					. $C_Target2 . "'"
					)
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Validate user and delete row from database table.
	 *
	 * Beware that, the stored procedure may also reject the delete!
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_delete()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->bsdriver->error_message( __( 'Delete is not allowed' ) );
		$this->bsdriver->Action = 'list';

		if ( $bsuser->is_admin() ) {
			if ( !$bsdriver->platform_test_token( $_GET['token'], $_GET['id'] ) ) {
				return $bsdriver->platform_access_denied();
			} elseif ( !$this->is_valid_dbinteger( 'ID', $_GET['id'] ) ) {
				$this->db_change(
					'P_ObjectiveDelete',
					'( %d, %d, %d )',
					array( $bsuser->OrgID, $bsuser->ID, $_GET['id'] ),
					'The objective has been deleted.',
					$this->db_messages
					);
			}
		}
	}

	/**
	 * Return a row of a gridview that user click edit on.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_edit_this( $row )
	{
		return '';
	}

	/**
	 * Return a row of a gridview that user click update on but had an error.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_update_this( $row )
	{
		return '';
	}

	/**
	 * Return a row of a gridview other than user click update or add.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_other( $row )
	{
		return '';
	}

	/**
	 * Return a row of a gridview that is only being displayed.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_list( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_ID            = $row[$bsdriver->Field->C_ID];
		$C_Title         = $row[$bsdriver->Field->C_Title];
		$C_Usage         = $row[$bsdriver->Field->C_Usage];
		$C_DeptTitle2    = $row[$bsdriver->Field->C_DeptTitle2];
		$C_Frequency     = $row[$bsdriver->Field->C_Frequency];
		$C_IsPublic      = $row[$bsdriver->Field->C_IsPublic];
		$C_FY1Title      = $row[$bsdriver->Field->C_FY1Title];
		$C_FY2Title      = $row[$bsdriver->Field->C_FY2Title];
		$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

		// Only admins can delete when row isn't only or in use
		$token        = $bsdriver->platform_get_token( $C_ID );
		$deleteButton = ( !$bsuser->is_admin() || $C_Usage > 0 )
			? ''
			: "&nbsp;<a onclick='javascript:return confirm(&#39;" . $this->OnDeleteMsg . "&#39;);' id='DeleteLinkButton_1' href='"
				. $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&sc_action=delete&token=' . $token . '&id=' . $C_ID . "'>" . 'Delete' . '</a>';

		return	ObjtrackerEasyStatic::table_td( '', $C_ID )
				. ObjtrackerEasyStatic::table_td( '', $C_Usage )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_DeptTitle2 ) )
				. ObjtrackerEasyStatic::table_td(
						'',
						"<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Objective&ID=' . $C_ID . "'>" . $C_Title
						)
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Frequency ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_IsPublic ) )
				. ObjtrackerEasyStatic::table_td( '', $C_FY1Title . ' ' . __( 'to' ) . ' ' . $C_FY2Title  )
				. ObjtrackerEasyStatic::table_td( '', $deleteButton );
	}

	/**
	 * Return a footer row of a gridview when there was an error in prior add.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_footer_error()
	{
		return '';
	}

	/**
	 * Return a footer row of a gridview.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_footer()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		return '';
	}

	/**
	 * Short Description
	 *
	 * Long Description
	 *
	 * @hook     action 'admin_notices'
	 * @since    1.0
	 * @param    type    $varname    Description
	 * @return   type                Description
	 * @return   bool	True/False
	 */
	function have_fiscalyears()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$results = $bsdriver->platform_db_query(
			'P_FiscalYearList',
			'( %d, %d )',
			array( $bsuser->OrgID, $bsuser->ID )
			);

		$this->fiscalyear_count = count( $results );
			
	}
}

?>
