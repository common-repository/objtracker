<?php
/**
 * Report on the target and measurements for all objectives over all years.
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
function bs_baseline( $bsdriver, $bsuser )
{
	include OBJTRACKER_NON2INC_DIR . 'class-baseline' . OBJTRACKER_CONTENT_MTYPE;

	if ( isset( $_GET['FY1'] ) ) {
		$C_FiscalYear1 = $_GET['FY1'];
//		$bsdriver->trace_text( ' get FiscalYear1=' . $C_FiscalYear1);
	} else {
		$C_FiscalYear1 = '';
//		$bsdriver->trace_text( ' noC_FiscalYear1=' . $C_FiscalYear1);
	}

	$configPage = new BsBaselinePage(
		$bsdriver,
		$bsuser,
		__( "Table's Columns" ),	// Parm: 3 - Title
		array(
			new ObjtrackerGvColumn( __( 'ID' ), ObjtrackerBaseline::_C_ID ),
			new ObjtrackerGvColumn( __( 'Status' ), ObjtrackerBaseline::_C_Status ),
			new ObjtrackerGvColumn( __( 'Dept' ), ObjtrackerBaseline::_C_Dept ),
			new ObjtrackerGvColumn( __( 'Objective' ), ObjtrackerBaseline::_C_Title ),
			new ObjtrackerGvColumn( __( 'Target' ), ObjtrackerBaseline::_C_Sort_sFyTarget ),
			),
		'P_Baseline',
		'( %d, %d )',
		array( $bsuser->OrgID, $bsuser->ID ),
		''
		);
	return $configPage->Response();

}
/**
 * Baseline processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsBaselinePage extends ObjtrackerConfigPage
{
	/**
	 * Holds the filtered measurements of all objectives
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $Objectives;
	
	/**
	 * Holds the filtered measurements of all objectives ready for filling a gridview
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $DataTable;
	
	/**
	 * Holds the filtered measurements of all objectives
	 *
	 * @var array
	 * @access private
	 * @since 1.0
	 */
	private $FiscalYears;			// Array of fiscal years to display

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
		$this->description();
		$prefix2      = $this->preface2();
		$this->Object = new ObjtrackerBaseline( $bsdriver, $bsuser, $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );

		$this->Object->gather_data();
		$this->Object->setup_dataset();
		$this->DataTable   = $this->Object->DataTable;
		$this->RowCount    = count( $this->DataTable );
		$this->FiscalYears = $this->Object->FiscalYears;

		foreach ( $this->Object->GvColumns as $column ) {
			array_push( $this->GvColumns, $column );
		}
		
		if ( $this->RowCount == 0 ) {
			$this->ValidationMsg = $bsdriver->error_message( __( 'Before using this page, objectives must be defined.' ) );
			$rowsReturned = '&nbsp;&nbsp;' . __( 'No rows returned' ) . "<br />\n";
			return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $this->_Description
				. $bsdriver->title_heading( __( 'Baseline' ) )
				. $this->ValidationMsg
				. $rowsReturned;
		} else {
			$rowsReturned = __( 'Count:' ) . ' ' . $this->RowCount . "<br />\n";
			return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $this->_Description
				. $bsdriver->title_heading( __( 'Baseline' ) )
				. $this->ValidationMsg
				. $rowsReturned
				. $this->gridview1()
				. $this->trailer();
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

		$this->setpage_description_head( __( 'Instructions' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text(
			__(
				'The table below lists each objective with its current year status,
				its final target value, and the last recorded measurement of each fiscal year'
				)
			);
		$this->description_list_start();
		$this->description_list_item( __( 'To view the definition of the status icon, hover the mouse over the icon.' ) );
		$this->description_list_item(
			__(
				'To sort the table, click on <b>ID</b>, <b>Status</b>, <b>Dept</b>,
				<b>Objective</b>, <b>Target</b>, or one of the fiscal years.'
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
	 * Retrieve objective from database.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function trailer()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		if ( $bsuser->is_admin() && $this->RowCount > 0 )
			return ( $this->RowCount == 0 )
				? ''
				: '<br />' . $bsdriver->extract_link( __( 'Spreadsheet Download' ), '' )
				. ' ' . $bsdriver->extract_link( __( 'Spreadsheet Download Raw' ), '&Raw=yes' );
		else
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
			$this->DataTable = objtracker_dataset_sort( $this->DataTable, $bsdriver->SortField, $this->SortDirection );
			$bsdriver->trace_text( 'HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );

			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='" . $bsdriver->SortField . "' />\n"
				. "<input type='hidden' name='SortHiddenDirection' value='" . $this->SortDirection . "' />\n";
			$sortUrl      = '&Ss=' . $this->SortDirection . ':' . $bsdriver->SortField;
		} else {
		//	$bsdriver->trace_text( 'No sorting, HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );
			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='' />\n"
				. "<input type='hidden' name='SortHiddenDirection' value='' />\n";
			$sortUrl      = '&Ss=';
		}

		// Write each row of gridview
		$tableBody  = '';
		$primaryRow = true;
		$id = 0;
		foreach ( $this->DataTable as $row ) {
			$id++;
			$tableBody .= $primaryRow ? ObjtrackerEasyStatic::table_tr_start( "class='BssGvOddRow'" ) : ObjtrackerEasyStatic::table_tr_start( "class='BssGvEvenRow'" );
			$primaryRow = $primaryRow ? false : true;
			$tableBody .= $this->gridview_row_list( $row, $id );
		}

		return	$hiddenValues
				. ObjtrackerEasyStatic::table_start( "class='BssGridview'" )
				. $this->sort_headers( $sortUrl )
				. $tableBody
				. ObjtrackerEasyStatic::table_end();
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
		for ( $i = 0; $i < ObjtrackerBaseline::_C_Sort_sFyTarget; $i++ ) {
			$gvColumn       = $this->GvColumns[$i];
			$dbColumnNumber = $gvColumn->DbColumnName;

			$headerText .= ObjtrackerEasyStatic::table_th(
				'',
				"<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
					. $bsdriver->MenuName . $bsdriver->PageState . '&sc_action=sort&fld='
					. $dbColumnNumber . $sortStatus . "'>" . $gvColumn->Title . '</a>'
				);
		}

		for ( $i = ObjtrackerBaseline::_C_Measurement; $i < count( $this->GvColumns ); $i += 2 ) {
			$gvColumn       = $this->GvColumns[$i];
			$dbColumnNumber = $gvColumn->DbColumnName; 

			$bsdriver->trace_text( ' col=(' . $dbColumnNumber . ',' . $gvColumn->Title . ') ' );
			$headerText .= ObjtrackerEasyStatic::table_th(
				'',
				"<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
					. $bsdriver->MenuName . $bsdriver->PageState . '&sc_action=sort&fld=' . $dbColumnNumber . $sortStatus . "'>" 
					. $gvColumn->Title . '</a>'
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

		$C_OID       = $row[ObjtrackerBaseline::_C_ID];
		$C_Status    = ObjtrackerEasyStatic::get_statusurl( $row[ObjtrackerBaseline::_C_Status] );
		$C_Dept      = $row[ObjtrackerBaseline::_C_Dept];
		$C_Title     = $row[ObjtrackerBaseline::_C_Title];
		$C_sFyTarget = $row[ObjtrackerBaseline::_C_sFyTarget];
		$C_PopUpText = $row[ObjtrackerBaseline::_C_PopUpText];
		$measures    = '';

		for ( $iFy = 0; $iFy < 2 * count( $this->FiscalYears ); $iFy += 2 ) {
			$C_Measurement = isset( $row[ObjtrackerBaseline::_C_Measurement + $iFy]) ? $row[ObjtrackerBaseline::_C_Measurement + $iFy] : 'x';

			$measures .= ObjtrackerEasyStatic::table_td( "style='text-align:right'", $C_Measurement );
		}
		return	ObjtrackerEasyStatic::table_td( '', $C_OID )
				. ObjtrackerEasyStatic::table_td(
					'',
					"<img class='BssButton' src='" . $bsdriver->PathImages . $C_Status . "' title='" . $C_PopUpText . "' />"
					)
				. ObjtrackerEasyStatic::table_td( '', $C_Dept )
				. ObjtrackerEasyStatic::table_td( '', $C_Title )
				. ObjtrackerEasyStatic::table_td( "style='text-align:right'", $C_sFyTarget  )
				. $measures
				;
	}
}
?>
