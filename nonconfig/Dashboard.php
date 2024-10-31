<?php
/**
 * Report of the target versus measured results for a single fiscal year.
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
function bs_dashboard( $bsdriver, $bsuser )
{
	include OBJTRACKER_NON2INC_DIR . 'class-dashboard' . OBJTRACKER_CONTENT_MTYPE;

	if ( isset( $_GET['FY1'] ) ) {
		$C_FiscalYear1 = $_GET['FY1'];
	} else {
		$C_FiscalYear1 = '';
	}

	$configPage = new ObjtrackerDashboard_page(
		$bsdriver,
		$bsuser,
		__( 'Not used' ),
		array(
				new ObjtrackerGvColumn( __( 'ID' ), ObjtrackerDashboard::_C_ID ),
				new ObjtrackerGvColumn( __( 'Status' ), ObjtrackerDashboard::_C_Status ),
				new ObjtrackerGvColumn( __( 'Category' ), ObjtrackerDashboard::_C_Category ),
				new ObjtrackerGvColumn( __( 'Dept' ), ObjtrackerDashboard::_C_Dept ),
				new ObjtrackerGvColumn( __( 'Objective' ), ObjtrackerDashboard::_C_Title ),
				new ObjtrackerGvColumn( __( 'Target' ), ObjtrackerDashboard::_C_Sort_Target ),
				new ObjtrackerGvColumn( __( 'Measurement' ), ObjtrackerDashboard::_C_Sort_Measurement ),
			),
		'P_Dashboard',
		'( %d, %d, %s )',
		array( $bsuser->OrgID, $bsuser->ID, $C_FiscalYear1 ),
		''
		);
	return $configPage->Response();

}
/**
 * Dashboard processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerDashboard_page extends ObjtrackerConfigPage
{
	const PARM_FISCALYEAR = 2;

	/**
	 * Holds the filtered measurements of all objectives
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $Objectives;
	/**
	 * Holds the filtered measurements of all objectives ready for filling gridview.
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $DataTable;

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
	//		$bsdriver->trace_text( 'get hidden Sortfield ' . $this->HiddenSortDirection . '  '. $this->HiddenSortField );
		} else {
			$this->HiddenSortField     = '' ;
			$this->HiddenSortDirection = '';
		}
	//	$bsdriver->trace_text( 'hidden(' . $this->HiddenSortDirection . $this->HiddenSortField . ')' );

		$message          = '';
		$this->ActionChar = substr( $bsdriver->Action, 0, 1 );
	//	$bsdriver->trace_text( ' action(' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

		switch ( $this->ActionChar ) {
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
			default:	// Initial or no action
				break;
		}
		$prefix  = $this->preface();
		$prefix2 = $this->preface2();
		if ( !isset( $this->FiscalYear1 ) ) {
			return
				$bsdriver->title_heading( __( 'Dashboard' ) )
				. $bsdriver->error_message( __( 'Before using this page, fiscal years then objectives must be defined.' ) );
		}
		$this->description();
		$this->Object = new ObjtrackerDashboard( $bsdriver, $bsuser, $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );

		$this->Object->gather_data();
		$this->Object->setup_dataset();
		$this->DataTable = $this->Object->DataTable;
		$this->RowCount  = count( $this->DataTable );

		$rowsReturned = $this->RowCount == 0
			? '&nbsp;&nbsp;' . __( 'No rows returned' ) . "<br />\n"
			: __( 'Count:' ) . ' ' . $this->RowCount . "<br />\n";

		return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $prefix
				. $this->_Description
				. $bsdriver->platform_start_form( '', '' )
				. $prefix2
				. $bsdriver->EndForm
				. $rowsReturned
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

		$bsdriver->PageState = '&FY1=' . $this->DbProcParms[self::PARM_FISCALYEAR];

		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text(
			__(
				'The table below lists the selected fiscal year objectives with their status, target value, and the last measurement of that fiscal year.'
				)
			);

		$this->description_list_start();
		$this->description_list_item( __( 'To view the definition of the status icon, hover the mouse over the icon.' ) );
		$this->description_list_item( __( 'To view other fiscal years, select from <b>Fiscal Year</b>.' ) );
		$this->description_list_item(
			__(
				'To sort the table, click on <b>ID</b>, <b>Status</b>, <b>Category</b>, <b>Dept</b>,'
				. ' <b>Objective</b>, <b>Target</b>, or <b>Measurement</b>.'
				)
			);
		if ( $bsuser->is_admin() ) {
			$this->description_list_item(
				__(
					'To extract a spreadsheet of these results, click on <b>Spreadsheet Download</b> or <b>Spreadsheet Download Raw</b>.'
					)
				);
		}

		$this->description_list_end();
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

		$timeDDL = $this->get_fiscalyear_dropdown(
			'C_FiscalYear1',
			$this->DbProcParms[self::PARM_FISCALYEAR],
			"onchange=\"BsOnChange1('" .
			$bsdriver->PathBase . $bsdriver->PlatformParm . "sc_menu=Dashboard&FY1=',this.form.C_FiscalYear1)\""
			);

		if ( $this->DbProcParms[self::PARM_FISCALYEAR] == '' ) {
			$this->DbProcParms = array( $bsuser->OrgID, $bsuser->ID, $this->FiscalYear1 );
	//		$bsdriver->trace_text( ' FiscalYear1=' . $this->FiscalYear1 );
		}

		return
			$bsdriver->title_heading( __( 'Dashboard' ) )
			. '<b>' . __( 'Fiscal Year:' ) . '</b> ' . $timeDDL
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

		return $bsuser->is_admin() && $this->RowCount > 0
			? $bsdriver->extract_link( __( 'Spreadsheet Download' ), '&A=C_Time&V=' . $this->DbProcParms[self::PARM_FISCALYEAR] )
				. ' '
				. $bsdriver->extract_link( __( 'Spreadsheet Download Raw' ), '&Raw=yes&A=C_Time&V=' . $this->DbProcParms[self::PARM_FISCALYEAR] )
			: '';
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
	//		$bsdriver->trace_text( 'sorting on '. $this->SortDirection . ' ' . $bsdriver->SortField );
			$this->DataTable = objtracker_dataset_sort( $this->DataTable, $bsdriver->SortField, $this->SortDirection );
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
		$id         = 0;

		foreach ( $this->DataTable as $row ) {
			$id++;
			$tableBody .= $primaryRow ? ObjtrackerEasyStatic::table_tr_start( "class='BssGvOddRow'" ) : ObjtrackerEasyStatic::table_tr_start( "class='BssGvEvenRow'" );

			$primaryRow = $primaryRow ? false : true;

			$tableBody .= $this->gridview_row_list( $row, $id );
			$tableBody .= ObjtrackerEasyStatic::table_tr_end();
		}

		return	$hiddenValues
				. ObjtrackerEasyStatic::table_start( "class='BssGridview'" )
				. $this->sort_headers( $sortUrl ) 
				. $tableBody
				. ObjtrackerEasyStatic::table_end(); // Close gridview and form
	}

	/**
	 * Return the column headers for a gridview.
	 *
	 * @since    1.0
	 * @params   string $sortStatus   Url parms to maintain state
	 * @returns  string               Html segment containing a gridview header
	 */
	public function sort_headers( $sortStatus )
	{
		$bsdriver = $this->bsdriver;

		$headerText = '';

		foreach ( $this->GvColumns as $gvColumn ) {
			$dbColumnNumber = $gvColumn->Title == 'ID' ? 0 : $gvColumn->DbColumnName;

			$headerText .= ObjtrackerEasyStatic::table_th(
				'',
				"<a href='" . $bsdriver->PathBase .
					$bsdriver->PlatformParm . 'sc_menu=' . $bsdriver->MenuName . $bsdriver->PageState .
					'&sc_action=sort&fld=' . $dbColumnNumber . $sortStatus .
					"'>" . $gvColumn->Title . '</a>'
				);
		}

		return
			ObjtrackerEasyStatic::table_tr_start( "class='BssGvHeader'" )
			. $headerText
			. ObjtrackerEasyStatic::table_tr_end();
	}


	/**
	 * Return a row of a gridview that is only being displayed.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @params   $id         Id number of the row
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_row_list( $row, $id )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_OID         = $row[ObjtrackerDashboard::_C_ID];
		$C_Status      = ObjtrackerEasyStatic::get_statusurl( $row[ObjtrackerDashboard::_C_Status] );
		$C_Category    = $row[ObjtrackerDashboard::_C_Category];
		$C_Dept        = $row[ObjtrackerDashboard::_C_Dept];
		$C_Title       = $row[ObjtrackerDashboard::_C_Title];
		$C_Target      = $row[ObjtrackerDashboard::_C_Target];
		$C_Measurement = isset( $row[ObjtrackerDashboard::_C_Measurement]) ? $row[ObjtrackerDashboard::_C_Measurement] : '';
		$C_PopUpText   = $row[ObjtrackerDashboard::_C_PopUpText];

		return	ObjtrackerEasyStatic::table_td( '', $C_OID )
				. ObjtrackerEasyStatic::table_td(
					'', "<img class='BssButton' src='" . $bsdriver->PathImages
					. $C_Status . "' title='" . $C_PopUpText . "' />"
					)
				. ObjtrackerEasyStatic::table_td( '', $C_Category )
				. ObjtrackerEasyStatic::table_td( '', $C_Dept )
				. ObjtrackerEasyStatic::table_td( '', $C_Title )
				. ObjtrackerEasyStatic::table_td( "style='text-align:right'", $C_Target )
				. ObjtrackerEasyStatic::table_td( "style='text-align:right'", $C_Measurement );
	}
}
?>
