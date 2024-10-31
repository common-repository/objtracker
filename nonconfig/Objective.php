<?php
/**
 * Manages display and edit of one objective.
 *
 *    Requirements
 *		o Only admins can update
 *		o Names of must be unique, stored proc checks
 *		o Link to list of objectives that match
 *		o Sortable column headers
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

include OBJTRACKER_NON2NON_DIR . 'ObjectiveGv1' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_NON2NON_DIR . 'ObjectiveGv2' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_NON2NON_DIR . 'ObjectiveGv3' . OBJTRACKER_CONTENT_MTYPE;


/**
 * Manages display and edit of one objective.
 *
 * @since    1.0
 * @param    object  $bsdriver   The environment object
 * @param    object  $bsuser     The user object
 * @return   string              Page's unique html text.
 */
function bs_objective( $bsdriver, $bsuser )
{
	$formview1 = new BsObjectivePage( $bsdriver, $bsuser, 'Objective' );
	$formview1 ->get_objective();
	if ( isset( $formview1->ValidationError ) )
		return $formview1->ValidationError;

	$gridview1 = new BsObjectiveGridView1(
		$bsdriver,
		$bsuser,
		__( 'Targets' ),	// Parm: Title
		array(				// Parm: Gridview columns
				new ObjtrackerGvColumn( __( 'Fiscal Year' ), 'C_FiscalYearTitle' ),
				new ObjtrackerGvColumn( __( 'Target' ), 'C_Target' ),
				new ObjtrackerGvColumn( __( 'Near(Green)' ), 'C_Target1' ),
				new ObjtrackerGvColumn( __( 'Far(Yellow)' ), 'C_Target2' ),
				new ObjtrackerGvColumn( __( 'Changed' ), 'C_Track_Changed' ),
				new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
				new ObjtrackerGvColumn( '', '' ),
			),
		'P_TargetObjList',
		'( %d, %d, %d )',
		array( $bsuser->OrgID, $bsuser->ID, $formview1->ObjectiveID ),
		'dummy-cannot delete measurements'
		);
	$gridview1->Formview1 = $formview1;

	$gridview2 = new BsObjectiveGridView2(
		$bsdriver,
		$bsuser,
		__( 'Measurements' ),		// Parm: Title
		array(			// Parm: Gridview columns
				new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
				new ObjtrackerGvColumn( __( 'Status' ), 'C_Status' ),
				new ObjtrackerGvColumn( __( 'Docs' ), 'C_Docs' ),
				new ObjtrackerGvColumn( __( 'Starting' ), 'C_PeriodStarting' ),
				new ObjtrackerGvColumn( __( 'Measure' ), 'C_Measurement' ),
				new ObjtrackerGvColumn( __( 'Notes' ), 'C_Notes' ),
				new ObjtrackerGvColumn( __( 'Changed' ), 'C_Track_Changed' ),
				new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
				new ObjtrackerGvColumn( '', '' ),
			),
		'P_MeasurementList',
		'( %d, %d, %d )',
		array( $bsuser->OrgID, $bsuser->ID, $formview1->ObjectiveID ),
		__( 'Are you sure you want to delete this measurement?' )
		);
	$gridview2->Formview1 = $formview1;

	$gridview3 = new BsObjectiveGridView3(
		$bsdriver,
		$bsuser,
		__( 'Missing' ),	// Parm: Title
		array(				// Parm: Gridview columns
				new ObjtrackerGvColumn( ' ', 'C_PeriodSorted' ),
				new ObjtrackerGvColumn( __( 'Period' ), 'C_PeriodStarting' ),
			),
		'P_MeasurementsMissing',
		'( %d, %d, %d )',
		array( $bsuser->OrgID, $bsuser->ID, $formview1->ObjectiveID ),
		''
		);
	$gridview3->Formview1 = $formview1;

	return
		$formview1->Response() . '<br />' 
		. $gridview1->Response() . '<br />' 
		. $gridview2->measurement_response( $gridview3 );
}

/**
 * Objective processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsObjectivePage extends ObjtrackerPage
{
	/**
	 * Holds the current objective ID
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	public $ObjectiveID;

	/**
	 * Holds the current metric type.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	public $MetricTypeID;

	/**
	 * Holds the current owner ID
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	public $OwnerID;

	/**
	 * Holds the users input
	 *
	 * @var ObjtrackerFormField
	 * @access private
	 * @since 1.0
	 */
	public $UserInput;

	/**
	 * Holds the current owner ID
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	const PARM_OBJECTIVEID = 2;

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
		$pageTitle
	)
	{
		parent::__construct(
			$bsdriver,
			$bsuser,
			$pageTitle
			);

		$this->db_messages = array(
			'ObjUpdTitle'		=> __( 'Title field is required' ),
			'ObjUpdDesc'		=> __( 'Descripton field is required' ),
			'ObjUpdDupTitle'	=> __( 'Title already exists' ),
			);
	}

	/**
	 * Returns this page unique html text.
	 *
	 * @since    1.0
	 * @return   string              Page's unique html text.
	 */
	function response()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->description();
		$this->ActionChar = $bsdriver->Panel == '0' ? substr( $bsdriver->Action, 0, 1 ) : self::BSPAGE_LIST;
		$bsdriver->trace_text( '<br />obj0panel(' . $bsdriver->Panel . ' ' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		if ( $this->ActionChar == self::BSPAGE_UPD && $bsuser->is_admin() ) {
			$this->item_update();
			$this->get_objective();
		}
		if ( $bsuser->should_show( ObjtrackerUser::UIShowDetail ) ) {
			return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $bsdriver->platform_start_form( '', '' )
				. "<input type='hidden' name='HiddenObjectiveID' value='" . $this->ObjectiveID . "' />\n"
				. "<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' /> \n"
				. "<input type='hidden' name='p' value='0' /> \n"
				. $this->_Description
				. $this->ValidationMsg
				. ObjtrackerEasyStatic::table_start( "cellspacing='0' id='BsFV1' class='BssFormview'" )
				. $this->row_title()
				. $this->row_types()
				. $this->row_fiscalyears()
				. $this->row_description()
				. $this->row_source()
				. $this->row_owner()
				. $this->row_metrictype()
				. ObjtrackerEasyStatic::table_end()
				. $bsdriver->EndForm;
		} else {
			return $this->_Description;
		}
	}

	/**
	 * Return line of display/edit form with user controls for the title.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the title.
	 */
	function row_title()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( !$bsuser->is_admin() ) {
			$cell = $this->FormviewResults[$this->FormviewFields->C_Title];
		} elseif ( $this->ActionChar == self::BSPAGE_EDIT ) {
			$C_Title = $this->FormviewResults[$this->FormviewFields->C_Title];
			$cell    = "<input type='text' name='C_Title' value='" . $C_Title . "' maxlength='100' />";
		} elseif ( $this->UserError ) {
			$cell = "<input type='text' name='C_Title' value='" . $this->UserInput->C_Title . "' maxlength='100' />";
		} else {
			$cell = $this->FormviewResults[$this->FormviewFields->C_Title];
		}

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Title' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( "colspan='2'",  $cell )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return line of display/edit form with user controls for the various types.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the various types.
	 */
	function row_types()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $bsuser->is_admin() ) {
			if ( $this->ActionChar == self::BSPAGE_EDIT || $this->UserError )
					$buttons = $bsdriver->Input0SubmitUpdate
					. $bsdriver->Input0SubmitCancel;
			else
				$buttons = "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
							. $bsdriver->MenuName . '&p=0&sc_action=edit&ID=' . $this->ObjectiveID . "'>" . __( 'Edit' ) . '</a>';
		} else {
			$buttons = '&nbsp;';
		}
		if ( $bsuser->is_admin() ) {
			$C_Frequency = $this->FormviewResults[$this->FormviewFields->C_Frequency];
			if ( $this->ActionChar == self::BSPAGE_EDIT ) {
				$C_IsPublic = $this->FormviewResults[$this->FormviewFields->C_IsPublic];
				$C_TypeID   = $this->FormviewResults[$this->FormviewFields->C_TypeID];

				return  ObjtrackerEasyStatic::table_tr_start( '' )
					. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Type' ) . '</b>' )
					. ObjtrackerEasyStatic::table_td(
						'', 
						$this->get_public_dropdown( $C_IsPublic ) . ':&nbsp;&nbsp;&nbsp;'
							. $this->get_objecttype_dropdown( $C_TypeID ) . ':&nbsp;&nbsp;&nbsp;'
							. $C_Frequency
						)
					. ObjtrackerEasyStatic::table_td( "style='text-align:right;'",  $buttons )
					. ObjtrackerEasyStatic::table_tr_end();
			} elseif ( $this->UserError ) {
				return  ObjtrackerEasyStatic::table_tr_start( '' )
					. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Type' ) . '</b>' )
					. ObjtrackerEasyStatic::table_td(
						'', 
						$this->get_public_dropdown( $this->UserInput->C_IsPublic ) . ':&nbsp;&nbsp;&nbsp;'
							. $this->get_objecttype_dropdown( $this->UserInput->C_TypeID ) . ':&nbsp;&nbsp;&nbsp;'
							. $C_Frequency
						)
					. ObjtrackerEasyStatic::table_td( "style='text-align:right;'",  $buttons )
					. ObjtrackerEasyStatic::table_tr_end();
		}
		}
		$C_IsPublic  = $this->FormviewResults[$this->FormviewFields->C_IsPublic];
		$C_Type      = $this->FormviewResults[$this->FormviewFields->C_Type];
		$C_Frequency = $this->FormviewResults[$this->FormviewFields->C_Frequency];

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Type' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $C_IsPublic . ':&nbsp;&nbsp;&nbsp;' . $C_Type . ':&nbsp;&nbsp;&nbsp;' . $C_Frequency )
				. ObjtrackerEasyStatic::table_td( "style='text-align:right;'",  $buttons )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return line of display/edit form with user controls for the fiscal year.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the fiscal year.
	 */
	function row_fiscalyears()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $bsuser->is_admin() ) {
			if ( $this->ActionChar == self::BSPAGE_EDIT ) {
				$C_FiscalYear1 = $this->FormviewResults[$this->FormviewFields->C_FiscalYear1];
				$C_FiscalYear2 = $this->FormviewResults[$this->FormviewFields->C_FiscalYear2];
				return "<tr><td><b>Fiscal Years</b></td><td colspan='2'>"
					. $this->get_fiscalyear_dropdown( 'C_FiscalYear1', $C_FiscalYear1, '' ) . ' ' . __( 'to' ) . ' '
					. $this->get_fiscalyear_dropdown( 'C_FiscalYear2', $C_FiscalYear2, '' ) . "</td></tr>\n";
			} elseif ( $this->UserError ) {
				return "<tr><td><b>Fiscal Years</b></td><td colspan='2'>"
					. $this->get_fiscalyear_dropdown( 'C_FiscalYear1', $this->UserInput->C_FiscalYear1, '' ) . ' ' . __( 'to' ) . ' '
					. $this->get_fiscalyear_dropdown( 'C_FiscalYear2', $this->UserInput->C_FiscalYear2, '' ) . "</td></tr>\n";
			}
		}
		$C_FY1Title = $this->FormviewResults[$this->FormviewFields->C_FY1Title];
		$C_FY2Title = $this->FormviewResults[$this->FormviewFields->C_FY2Title];

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Fiscal Years' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( "colspan='2'", $C_FY1Title . ' ' . __( 'to' ) . ' ' . $C_FY2Title )
				. ObjtrackerEasyStatic::table_tr_end();

	}

	/**
	 * Return line of display/edit form with user controls for the description.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the description.
	 */
	function row_description()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $bsuser->is_admin() ) {
			$cell = '';
			if ( $this->ActionChar == self::BSPAGE_EDIT ) {
				$C_Description = $this->FormviewResults[$this->FormviewFields->C_Description];
				$cell = "<textarea name='C_Description' rows='6' cols='130' maxlength='1024'>" . $C_Description . '</textarea>';
				$useShiftEnter = '<br /><br />' . __( 'Use Shift+Enter for newline' );
			} elseif ( $this->UserError ) {
				$cell = "<textarea name='C_Description' rows='6' cols='130' maxlength='1024'>" . $this->UserInput->C_Description . '</textarea>';
				$useShiftEnter = '<br /><br />' . __( 'Use Shift+Enter for newline' );
			}
			if ( $cell != '' ) {
				return "<tr><td style='vertical-align: top;'><b>" . __( 'Description' ) . '</b>' . $useShiftEnter . "</td><td colspan='2'>" . $cell . "</td></tr>\n";
			}
		}
		$cell = $this->FormviewResults[$this->FormviewFields->C_Description];
		$desc = preg_split( "/\n/", $cell );
		$cell = implode( '<br/>', $desc );

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( "style='vertical-align: top;'", '<b>' . __( 'Description' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( "colspan='2'", $cell )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return line of display/edit form with user controls for the source.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the source.
	 */
	function row_source()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( !$bsuser->is_admin() ) {
			$cell = $this->FormviewResults[$this->FormviewFields->C_Source];
		} elseif ( $this->ActionChar == self::BSPAGE_EDIT ) {
			$C_Source = $this->FormviewResults[$this->FormviewFields->C_Source];
			$cell     = "<input type='text' name='C_Source' value='" . $C_Source . "' maxlength='100' />";
		} elseif ( $this->UserError ) {
			$cell = "<input type='text' name='C_Source' value='" . $this->UserInput->C_Source . "' maxlength='100' />";
		} else {
			$cell = $this->FormviewResults[$this->FormviewFields->C_Source];
		}

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Source' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( "colspan='2'", $cell )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return line of display/edit form with user controls for the owner.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the owner.
	 */
	function row_owner()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $bsuser->is_admin() ) {
			$C_Department = $this->FormviewResults[$this->FormviewFields->C_Department];
			$C_Frequency  = $this->FormviewResults[$this->FormviewFields->C_Frequency];

			if ( $this->ActionChar == self::BSPAGE_EDIT ) {
				$C_OwnerID = $this->FormviewResults[$this->FormviewFields->C_OwnerID];
				return "<tr><td><b>Owner</b></td><td colspan='2'>"
					. $this->get_owner_dropdown( $C_OwnerID, 'AnyUser' ) . ' of ' . $C_Department . "</td></tr>\n";
			} elseif ( $this->UserError ) {
				return "<tr><td><b>Owner</b></td><td colspan='2'>"
					. $this->get_owner_dropdown( $this->UserInput->C_OwnerID, 'AnyUser' ) . ' of ' . $C_Department . "</td></tr>\n";
			}
		}
		$C_Owner      = $this->FormviewResults[$this->FormviewFields->C_Owner];
		$C_Department = $this->FormviewResults[$this->FormviewFields->C_Department];

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Owner' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( "colspan='2'", $C_Owner . ' ' . __( 'of' ) . ' ' . $C_Department )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return line of display/edit form with user controls for the metric type.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the metric type.
	 */
	function row_metrictype()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_MetricType     = $this->FormviewResults[$this->FormviewFields->C_MetricType];
		$C_MetricTypeDesc = $this->FormviewResults[$this->FormviewFields->C_MetricTypeDesc];
		$by               = $this->FormviewResults[$this->FormviewFields->C_Track_Changed] . ' by '
							. $this->FormviewResults[$this->FormviewFields->C_Track_Userid];

		return  ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>Metric Type</b>' )
				. ObjtrackerEasyStatic::table_td( '', $C_MetricType . ', <b>Format:</b> ' . $C_MetricTypeDesc )
				. ObjtrackerEasyStatic::table_td( '', '<b>Changed</b> ' . $by )
				. ObjtrackerEasyStatic::table_tr_end();

	}

	/**
	 * Validate user input and update the objective.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function item_update()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_WasFiscalYear1 = $this->FormviewResults[$this->FormviewFields->C_FiscalYear1];
		$C_WasFiscalYear2 = $this->FormviewResults[$this->FormviewFields->C_FiscalYear2];

		$this->UserInput                = new ObjtrackerFormField();
		$this->UserInput->C_FiscalYear1 = trim( $_POST['C_FiscalYear1'] );
		$this->UserInput->C_FiscalYear2 = trim( $_POST['C_FiscalYear2'] );
		$this->UserInput->C_OwnerID     = trim( $_POST['C_OwnerID'] );
		$this->UserInput->C_IsPublic    = trim( $_POST['C_IsPublic'] );
		$this->UserInput->C_Source      = trim( $_POST['C_Source'] );
		$this->UserInput->C_TypeID      = trim( $_POST['C_ObjectiveTypeID'] );
		$this->UserInput->C_Title       = trim( $_POST['C_Title'] );
		$this->UserInput->C_IsPublic    = trim( $_POST['C_IsPublic'] );
		$this->UserInput->C_Description = trim( $_POST['C_Description'] );

		if ( !$this->is_valid_dbinteger( 'ID', $this->ObjectiveID ) ) {
		} elseif ( !$this->is_valid_dbparm( 100, __( 'Title' ), $this->UserInput->C_Title ) ) {
		} elseif ( !$this->is_valid_dbparm( 1024, __( 'Description' ), $this->UserInput->C_Description ) ) {
		} elseif ( $this->UserInput->C_FiscalYear1 > $this->UserInput->C_FiscalYear2 ) {
			$this->UserError     = true;
			$this->ValidationMsg = $bsdriver->error_message( __( 'Fiscal year 1 must not be greater than fiscal year 2.' ) );
		} elseif ( !$this->is_valid_dbparm( 100, __( 'Source' ), $this->UserInput->C_Source, false ) ) {
		} else {
			$this->db_change(
				'P_ObjectiveUpdate',
				'(  %d, %d, %d, %d, %d, %d, %d, %s, %s, %s, %s, %s, %s )',
				array(
						$bsuser->OrgID, $bsuser->ID,
						$this->ObjectiveID,
						$this->UserInput->C_FiscalYear1,
						$this->UserInput->C_FiscalYear2,
						$C_WasFiscalYear1,
						$C_WasFiscalYear2,
						$this->UserInput->C_OwnerID,
						$this->UserInput->C_IsPublic,
						$this->UserInput->C_Source,
						$this->UserInput->C_TypeID,
						$this->UserInput->C_Title,
						$this->UserInput->C_Description,
					),
				'',
				$this->db_messages
			);
		}
	}

	/**
	 * Retrieve the objective from the database.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function get_objective()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( isset( $_POST['HiddenObjectiveID'] ) ) {
			$this->ObjectiveID = $_POST['HiddenObjectiveID'];
		} elseif ( isset( $_GET['ID'] ) ) {
			$this->ObjectiveID = $_GET['ID'];

			// See if user is showing or hiding
			$uiSettings = $bsuser->UiSettings;
			if ( isset( $_GET['sd'] ) )
				$bsuser->should_update( ObjtrackerUser::UIShowDetail, $_GET['sd'] );
			if ( isset( $_GET['st'] ) )
				$bsuser->should_update( ObjtrackerUser::UIShowTargets, $_GET['st'] );
			if ( isset( $_GET['sm'] ) )
				$bsuser->should_update( ObjtrackerUser::UIShowMeasurements, $_GET['sm'] );
			if ( $uiSettings != $bsuser->UiSettings ) {
				$bsdriver->platform_db_query(
					'P_PersonUpdateUI',
					'( %d, %d, %s )',
					array( $bsuser->OrgID, $bsuser->ID, $bsuser->UiSettings )
					);
			}
		} else {
			$this->ValidationError = $bsdriver->error_message( __( 'ERROR: ObjectiveID not found(0).' ) );
			return;
		}

		$bsdriver->trace_text( ' ID(' . $this->ObjectiveID . ')' );
		$this->FormviewResults = $bsdriver->platform_db_query(
			'P_Objective',
			'( %d, %d, %d, %s )',
			array( $bsuser->OrgID, $bsuser->ID, $this->ObjectiveID, 'Label' )
			);
		if ( count( $this->FormviewResults ) == 1 ) {
			$this->FormviewResults = $this->FormviewResults[0];
			$this->FormviewFields  = $bsdriver->Field;
			$this->OwnerID         = $this->FormviewResults[$this->FormviewFields->C_OwnerID];
			$this->MetricTypeID    = $this->FormviewResults[$this->FormviewFields->C_MetricTypeID];
		} else {
			$this->ValidationError = $bsdriver->error_message( __( 'ERROR: Objective not found.' ) );
		}
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

		$bsdriver->PageState = '&ID=' . $this->ObjectiveID;
		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text( __( 'Below is the detail of the selected objective, its fiscal year target values, and measurements' ) );
		$this->description_end();
		$part1 = $this->_Description;


		$bsdriver->PageState = '&ID=' . $this->ObjectiveID;

		$this->setpage_description_head( __( 'Objective Definition' ), ObjtrackerUser::UIShowDetail, '' );
		if ( $bsuser->should_show( ObjtrackerUser::UIShowDetail ) ) {
			$this->description_text( __( 'To change the objective, click on <b>Edit</b>, change values, and click on <b>Update</b>.' ) );
		}
		$this->description_end();
		$part2              = $this->_Description;
		$this->_Description = $part1 . $part2;
	}
}

?>
