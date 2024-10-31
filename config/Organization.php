<?php
/**
 * Administrator's page for managing the organization values.
 *
 *	 Requirements:
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
/**
 * Returns this page unique html text.
 *
 * @since    1.0
 * @param    object  $bsdriver   The environment object
 * @param    object  $bsuser     The user object
 * @return   string              Page's unique html text.
 */

function bs_organization( $bsdriver, $bsuser )
{
	$configPage = new BsOrganizationPage(
		$bsdriver,
		$bsuser,
		__( 'Organization' )
		);
	return $configPage->Response();
}

/**
 * Organization processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsOrganizationPage extends ObjtrackerPage
{
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
			'OrgInsTitle'	=> __( 'Title field is required' ),
			'OrgInsPath'	=> __( 'Upload path is required' ),
			'OrgInsPath'	=> __( 'Title already exists' ),
			'OrgUpdTitle'	=> __( 'Title is required' ),
			'OrgUpdPath'	=> __( 'Upload path is required' ),
			'OrgUpdTitle'	=> __( 'Title is required' ),
			'OrgUpdTitle2'	=> __( 'Short Title is required' ),
			'OrgUpdateDup'	=> __( 'Upload path is required' ),
			'OrgUpdTitle'	=> __( 'Title is required' ),
			'OrgUpdTitle2'	=> __( 'Short Title is required' ),
			'OrgUpdPath'	=> __( 'Upload path is required' ),
			);
	}

	/**
	 * Having a fiscal year means that the 1st month of the fiscal year can't be changed.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	private $FiscalYear_Count;

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

		$this->have_fiscalyears();

		$this->description();
		$this->ActionChar = substr( $bsdriver->Action, 0, 1 );
		$bsdriver->trace_text( ' action(' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		if ( $this->ActionChar == self::BSPAGE_UPD && $bsuser->is_admin() ) {
			$this->row_update();
		}

		$this->get_organization();

		$this->description();

		return
			"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
			. $bsdriver->platform_start_form( '', '' )
			. " <input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />\n"
			. $this->_Description
			. $txt = $bsdriver->description_headertext( __( 'Organization' ) )
			. $this->ValidationMsg
			. ObjtrackerEasyStatic::table_start( "cellspacing='0' id='BsFV1' class='BssFormview' style='width:700px;border-collapse:collapse;'" )
			. $this->row_editupdate()
			. $this->row_title()
			. $this->row_shorttitle()
			. $this->row_firstmonth()
			. $this->row_uploadpath()
			. $this->row_trailer()
			. $this->row_changed()
			. ObjtrackerEasyStatic::table_end()
			. $bsdriver->EndForm;
	}

	/**
	 * Return line of display/edit form with user controls for edit or update.
	 *
	 * @since    1.0
	 * @returns  string        User controls for edit or update.
	 */
	function row_editupdate()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $bsuser->is_admin() ) {
			if ( $this->ActionChar == self::BSPAGE_EDIT || $this->UserError )
				$buttons = $bsdriver->Input0SubmitUpdate
					. $bsdriver->Input0SubmitCancel;
			else
				$buttons = "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
					. $bsdriver->MenuName . "&sc_action=edit'>" . __( 'Edit' ) . '</a>';

		return ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td( "style='width:25%'", '&nbsp;' )
			. ObjtrackerEasyStatic::table_td( "style='text-align: right'", $buttons )
			. ObjtrackerEasyStatic::table_tr_end();
		} else {
			return '';
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
			$cell = $this->FormviewResults[$bsdriver->Field->C_Title];
		} elseif ( $this->ActionChar == self::BSPAGE_EDIT ) {
			$C_Title = $this->FormviewResults[$bsdriver->Field->C_Title];
			$cell    = "<input type='text' name='C_Title' value='" . stripslashes( $C_Title ) . "' maxlength='64' />";
		} elseif ( $this->UserError ) {
			$cell = "<input type='text' name='C_Title' value='" . stripslashes( $_POST['C_Title'] ) . "' maxlength='64' />";
		} else {
			$cell = $this->FormviewResults[$bsdriver->Field->C_Title];
		}

		return ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Title' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $cell )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return line of display/edit form with user controls for the short title.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the short title.
	 */
	function row_shorttitle()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( !$bsuser->is_admin() ) {
			$cell = $this->FormviewResults[$bsdriver->Field->C_ShortTitle];
		} elseif ( $this->ActionChar == self::BSPAGE_EDIT ) {
			$C_ShortTitle = $this->FormviewResults[$bsdriver->Field->C_ShortTitle];
			$cell         = "<input type='text' name='C_ShortTitle' value='" . stripslashes( $C_ShortTitle ) . "' maxlength='32' />";
		} elseif ( $this->UserError ) {
			$cell = "<input type='text' name='C_ShortTitle' value='" . stripslashes( $_POST['C_ShortTitle'] ) . "' maxlength='32' />";
		} else {
			$cell = $this->FormviewResults[$bsdriver->Field->C_ShortTitle];
		}

		return ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Short Title' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $cell )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return line of display/edit form with user controls for the 1st month.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the 1st month.
	 */
	function row_firstmonth()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$commonmessage = __( 'Fiscal years have been defined - update of first month is disabled' );
		$help          = '';
		$C_FirstMonth  = $this->FormviewResults[$bsdriver->Field->C_FirstMonth];
		if ( !$bsuser->is_admin() ) {
			$cell = $this->FormviewResults[$bsdriver->Field->C_FirstMonth];
		} elseif ( $this->ActionChar == self::BSPAGE_EDIT ) {
			if ( $this->FiscalYear_Count > 0 ) {
				$cell = $this->get_firstmonth_dropdown(
							$this->FormviewResults[$bsdriver->Field->C_FirstMonth],
							"disabled='disabled'"
							)
						. " <input type='hidden' name='C_FirstMonth' value='" . $C_FirstMonth . "' />\n";

				$help = ObjtrackerEasyStatic::table_tr_start( '' )
						. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
						. ObjtrackerEasyStatic::table_td( '', '<b>' . $commonmessage . '</b>' )
						. ObjtrackerEasyStatic::table_tr_end();
			} else {
				$cell = $this->get_firstmonth_dropdown( $C_FirstMonth, '' );
			}
		} elseif ( $this->UserError ) {
			if ( $this->FiscalYear_Count > 0 ) {
				$cell = $this->get_firstmonth_dropdown( $_POST['C_FirstMonth'], "disabled='disabled'" )
						. " <input type='hidden' name='C_FirstMonth' value='" . $C_FirstMonth . "' />\n";
				$help = ObjtrackerEasyStatic::table_tr_start( '' )
						. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
						. ObjtrackerEasyStatic::table_td( '', '<b>' . $commonmessage . '</b>' )
						. ObjtrackerEasyStatic::table_tr_end();
			} else {
				$cell = $this->get_firstmonth_dropdown( $_POST['C_FirstMonth'], '' );
			}
		} else {
			$cell = $this->FormviewResults[$bsdriver->Field->C_FirstMonth]
					. ' (' . date( 'F', mktime( 0, 0, 0, $this->FormviewResults[$bsdriver->Field->C_FirstMonth], 10 ) ) . ')';
		}

		return ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'First Month' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $cell )
						. ObjtrackerEasyStatic::table_tr_end()
			. $help;
	}

	/**
	 * Return dropdown box for 1st month of fiscal year.
	 *
	 * @since    1.0
	 * @returns  string        Dropdown box for 1st month of fiscal year.
	 */
	function get_firstmonth_dropdown( $value, $disabled )
	{
		$selectV1  = $value == 1 ? "selected='selected' " : ' ';
		$selectV4  = $value == 4 ? "selected='selected' " : ' ';
		$selectV7  = $value == 7 ? "selected='selected' " : ' ';
		$selectV10 = $value == 10 ? "selected='selected' " : ' ';
		return
			"<select name='C_FirstMonth' id='C_FirstMonth' " . $disabled . ">\n"
				. ' <option ' . $selectV1 . "value='1'>" . date( 'F', mktime( 0, 0, 0, 1, 10 ) ) . "</option>\n"
				. ' <option ' . $selectV4 . "value='4'>" . date( 'F', mktime( 0, 0, 0, 4, 10 ) ) . "</option>\n"
				. ' <option ' . $selectV7 . "value='7'>" . date( 'F', mktime( 0, 0, 0, 7, 10 ) ) . "</option>\n"
				. ' <option ' . $selectV10 . "value='10'>" . date( 'F', mktime( 0, 0, 0, 10, 10 ) ) . "</option>\n"
				. "</option>\n";
	}

	/**
	 * Return line of display/edit form with user controls for the upload path.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the upload path.
	 */
	function row_uploadpath()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$commonmessage = __( "For Windows servers: Use slashes ('/') instead of back slashes ('\\')." );
		$help = '';
		if ( !$bsuser->is_admin() ) {
			$cell = $this->FormviewResults[$bsdriver->Field->C_UploadFsPath];
		} elseif ( $this->ActionChar == self::BSPAGE_EDIT ) {
			$C_UploadFsPath = $this->FormviewResults[$bsdriver->Field->C_UploadFsPath];
			$cell = "<input type='text' name='C_UploadFsPath' value='" . $this->fix_slashes( $C_UploadFsPath ) . "' maxlength='150' />";
			$help = ObjtrackerEasyStatic::table_tr_start( '' )
					. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
					. ObjtrackerEasyStatic::table_td( '', '<b>' . $commonmessage . '</b>' )
					. ObjtrackerEasyStatic::table_tr_end();
		} elseif ( $this->UserError ) {
			$cell = "<input type='text' name='C_UploadFsPath' value='" . $_POST['C_UploadFsPath'] . "' maxlength='150' />";
			$help = ObjtrackerEasyStatic::table_tr_start( '' )
					. ObjtrackerEasyStatic::table_td( '', '&nbsp;' )
					. ObjtrackerEasyStatic::table_td( '', '<b>' . $commonmessage . '</b>' )
					. ObjtrackerEasyStatic::table_tr_end();
		} else {
			$cell = $this->FormviewResults[$bsdriver->Field->C_UploadFsPath];
		}

		return ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Upload path' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $cell )
				. ObjtrackerEasyStatic::table_tr_end()
				. $help;
	}

	/**
	 * Cleanup upload path.
	 *
	 * @since    1.0
	 * @returns  string        Cleaned upload path.
	 */
	function fix_slashes( $txt )
	{
		return $txt; //str_replace( '\\','\\\\', $txt );
	}

	/**
	 * Return line of display/edit form with user controls for the page trailer.
	 *
	 * @since    1.0
	 * @returns  string        User controls for the page trailer.
	 */
	function row_trailer()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( !$bsuser->is_admin() ) {
			$cell = $this->FormviewResults[$bsdriver->Field->C_Trailer];
		} elseif ( $this->ActionChar == self::BSPAGE_EDIT ) {
			$C_Trailer = $this->FormviewResults[$bsdriver->Field->C_Trailer];
			$cell = "<input type='text' name='C_Trailer' value='" . stripslashes( $C_Trailer ) . "' maxlength='48' />";
		} elseif ( $this->UserError ) {
			$cell = "<input type='text' name='C_Trailer' value='" . stripslashes( $_POST['C_Trailer'] ) . "' maxlength='48' />";
		} else {
			$cell = $this->FormviewResults[$bsdriver->Field->C_Trailer];
		}

		return ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Trailer' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $cell )
				. ObjtrackerEasyStatic::table_tr_end();
	}

	/**
	 * Return line of display/edit form with info on last change.
	 *
	 * @since    1.0
	 * @returns  string        Info on last change.
	 */
	function row_changed()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$cell = $this->FormviewResults[$bsdriver->Field->C_Track_Changed] . ' by '
				. $this->FormviewResults[$bsdriver->Field->C_Track_Userid];

		return ObjtrackerEasyStatic::table_tr_start( '' )
				. ObjtrackerEasyStatic::table_td( '', '<b>' . __( 'Changed' ) . '</b>' )
				. ObjtrackerEasyStatic::table_td( '', $cell )
				. ObjtrackerEasyStatic::table_tr_end();
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

		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text(
			__(
				'Fields are defined here so that they are not hard coded in the web application or in the stored procedures.'
				)
			);
		$this->description_list_start();
		if ( $bsuser->is_admin() ) {
			$this->description_list_item(
			__(
				'To edit these values, click on <b>Edit</b>, change values, and click on <b>Update</b>.'
				)
			);
		}
		$this->description_list_end();
		$this->description_end();
	}

	/**
	 * Validate user input and update a row in database table.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_update()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_Title        = trim( $_POST['C_Title'] );
		$C_ShortTitle   = trim( $_POST['C_ShortTitle'] );
		$C_UploadFsPath = $bsdriver->cleanup_dbfilename( trim( $_POST['C_UploadFsPath'] ) );
		$C_Trailer      = trim( $_POST['C_Trailer'] );

		if ( !$this->is_valid_dbinteger( 'ID', $bsuser->OrgID ) ) {
		} elseif ( !$this->is_valid_dbparm( 64, __( 'Title' ), $C_Title ) ) {
		} elseif ( !$this->is_valid_dbparm( 16, __( 'Short title' ), $C_ShortTitle ) ) {
		} elseif ( !$this->is_valid_dbparm( 150, __( 'Upload path' ), $C_UploadFsPath ) ) {
		} elseif ( !$this->is_valid_dbparm( 48, __( 'Trailer' ), $C_Trailer ) ) {
		} elseif ( !$this->is_valid_dbpath( $C_UploadFsPath ) ) {
		} elseif ( $this->FiscalYear_Count > 0  ) {
			$bsdriver->trace_text( 'FixedYes=' . $C_UploadFsPath );

			$this->db_change(
				'P_OrganizationUpdateB',
				'( %d, %d, %s, %s, %s, %s, %s )',
				array(
					$bsuser->OrgID, $bsuser->ID,
					$C_Title, $C_ShortTitle, 'No', $C_UploadFsPath, $C_Trailer,
					),
				__( 'The objective type has been updated.' ),
				$this->db_messages
			);
		} else {
			$C_FirstMonth = $_POST['C_FirstMonth'];

			$this->db_change(
				'P_OrganizationUpdate',
				'( %d, %d, %s, %s, %s, %d, %s, %s, %s )',
				array(
					$bsuser->OrgID, $bsuser->ID,
					$C_Title, $C_ShortTitle, $C_FirstMonth, 'No', $C_UploadFsPath, $C_Trailer,
					),
				__( 'The objective type has been updated.' ),
				$this->db_messages
				);
		}
	}


	/**
	 * Retrieve the organization from the database.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function get_organization()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$this->FormviewResults = $bsdriver->platform_db_query(
			'P_OrganizationList',
			'( %d, %d )',
			array( $bsuser->OrgID, $bsuser->ID )
			);
		if ( count( $this->FormviewResults ) ) {
			$this->FormviewResults = $this->FormviewResults[0];
		}
	}

	/**
	 * Determine if the first month is now fixed.
	 *
	 * @since    1.0
	 * @returns  void
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

		$this->FiscalYear_Count = count( $results );
	}
}

?>
