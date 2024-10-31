<?php
/**
 * Administrator's page for extending the end fiscal year of multiple objectives.
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
function bs_extendendfy( $bsdriver, $bsuser )
{
	if ( !$bsuser->is_admin() )
		return __( 'Only allowed for administrators' );

	if ( isset( $_GET['FY2'] ) ) {
		$C_FiscalYear2 = $_GET['FY2'];
	} elseif ( isset( $_POST['FY2'] ) ) {
		$C_FiscalYear2 = $_POST['FY2'];
	} else {
		$C_FiscalYear2 = '';
	}

	$configPage = new BsExtendEndFyPage(
		$bsdriver,
		$bsuser,
		__( 'Extend End Fiscal Year' ),		// Parm: Title
		array(							// Parm: Gridview columns
				new ObjtrackerGvColumn( __( 'Box' ), 'Box' ),
				new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
				new ObjtrackerGvColumn( __( 'Fiscal Years' ), 'C_FY1Title' ),
				new ObjtrackerGvColumn( __( 'Department' ), 'C_DeptTitle2' ),
				new ObjtrackerGvColumn( __( 'Objective' ), 'C_Title' ),
			),
		'P_ExtendList',
		'( %d, %d, %d )',
		array( $bsuser->OrgID, $bsuser->ID, $C_FiscalYear2 ),
		'?'
		);
	return $configPage->Response();
}

/**
 * Extending fiscal year processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsExtendEndFyPage extends ObjtrackerConfigPage
{
	const PARM_FISCALYEAR2 = 2;

	/**
	 * Holds the selected fiscal year 2 value for objectives to be extended.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	public $C_FiscalYear2;

	/**
	} elseif ( substr( $name,-12, 12 ) == 'Download.php' ) {
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
	//		$bsdriver->trace_text( 'get hidden Sortfield ' . $this->HiddenSortDirection . ' ' . $this->HiddenSortField );
		} else {
			$this->HiddenSortField     = '' ;
			$this->HiddenSortDirection = '';
		}
	//	$bsdriver->trace_text( 'hidden(' . $this->HiddenSortDirection . $this->HiddenSortField . ')' );

		$message = '';
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
			case self::BSPAGE_ADD:
				$this->row_insert();
				$this->DbProcParms = array( $bsuser->OrgID, $bsuser->ID, '' );
				break;
			case self::BSPAGE_LIST:
				break;
			default:	// Initial or no action
				break;
		}
		$prefix  = $this->preface();
		$prefix2 = $this->preface2();
		$this->description();

		$this->GvResults = $bsdriver->platform_db_query( $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );
		$this->RowCount  = count( $this->GvResults );
		if ( $this->RowCount == 0 ) {
			$rowsReturned = '&nbsp;&nbsp;' . __( 'No rows returned' ) . "<br />\n";
			$button = '';
		} else {
			$rowsReturned = __( 'Count:' ) . ' ' . $this->RowCount . "\n";
			$button = "<input type='submit' name='submit0Add' value='Extend' class='BssButton' style='position:relative;left:90px;/>";
		}

		return
			"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
			. $bsdriver->platform_start_form( '', '' )
			. " <input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' />\n"
			. " <input type='hidden' name='FY2' value='" . $this->FiscalYear2 . "' />\n"
			. $prefix
			. $this->_Description
			. $prefix2
			. $rowsReturned
			. $button
			. $this->gridview1()
			. $bsdriver->EndForm;
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

		$timeDDL = $this->get_fiscalyear2_dropdown(
			'C_FiscalYear2',
			$this->DbProcParms[self::PARM_FISCALYEAR2],
			"onchange=\"BsOnChange1('" . $bsdriver->PathBase . $bsdriver->PlatformParm
				. "sc_menu=Admin-ExtendEndFy&FY2=',this.form.C_FiscalYear2)\""
			);

		if ( $this->DbProcParms[self::PARM_FISCALYEAR2] == '' ) {
			$this->DbProcParms = array( $bsuser->OrgID, $bsuser->ID, $this->FiscalYear2 );
		}

		return
				$bsdriver->title_heading( __( 'Extend End Fiscal Year' ) )
				. '<b>End Fiscal Year:</b> ' . $timeDDL
				. "<br />\n";
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
		$this->description_text( __( 'This page faciliates adding another fiscal year to many objectives.' ) );

		$this->description_text( __( 'Those objectives with check marks will be extended when you click on Extend.' ) );
		$this->description_list_start();
		$this->description_list_item(
			__(
				'To sort the table, click on <b>ID</b>, <b>Fiscal Years</b>, <b>Dept</b>, or <b>Objective</b>.'
				)
			);
		$this->description_list_item(
			__(
				'To extend a few objectives, check the <b>ID</b> box for those objectives and click on <b>Extend</b>.'
				)
			);
		$this->description_list_item(
			__(
				'To extend many objectives, check the <b>ID</b> box in the header column, '
				. 'uncheck the ID box for those objectives that are not to be extended and click on <b>Extend</b>.'
				)
			);

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

		$bsdriver->trace_text( 'gridview1' );
		$this->on_db_results();

		// If sorting, sort the data, and sort sort state in hidden fields
		if ( strlen( $this->SortDirection ) > 0 ) {
	//		$bsdriver->trace_text( 'sorting on '. $this->SortDirection . ' ' . $bsdriver->SortField );
			$this->GvResults = objtracker_dataset_sort( $this->GvResults, $bsdriver->SortField, $this->SortDirection );
			$bsdriver->trace_text( 'HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );

			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='" . $bsdriver->SortField . "' />\n"
				. "<input type='hidden' name='SortHiddenDirection' value='" . $this->SortDirection . "' />\n";

			$sortUrl = '&Ss=' . $this->SortDirection . ':' . $bsdriver->SortField;
		} else {
	//		$bsdriver->trace_text( 'No sorting, HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );
			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='' />\n"
				. "<input type='hidden' name='SortHiddenDirection' value='' />\n";
			$sortUrl = '&Ss=';
		}

		// Write each row of gridview
		$tableBody  = '';
		$primaryRow = true;
		$id         = 0;
		foreach ( $this->GvResults as $row ) {
			$id++;
			$tableBody .= $primaryRow ? ObjtrackerEasyStatic::table_tr_start( "class='BssGvOddRow'" ) : ObjtrackerEasyStatic::table_tr_start( "class='BssGvEvenRow'" );
			$primaryRow = $primaryRow ? false : true;

			$tableBody .= $this->gridview_row_list( $row, $id );

			$tableBody .= ObjtrackerEasyStatic::table_tr_end();
		}

		return	$hiddenValues
				. ObjtrackerEasyStatic::table_start( "class='BssGridview' id='TABLE'" )
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
		$bsdriver   = $this->bsdriver;
		$headerText = '';

		foreach ( $this->GvColumns as $gvColumn ) {
			if ( $gvColumn->Title == 'Box' ) {
				$headerText .= ObjtrackerEasyStatic::table_th( '', "<input type='checkbox' name='Header' onclick=\"changeAllCheckBoxes(this);\">" );
			} else {
				$headerText .= ObjtrackerEasyStatic::table_th(
					'',
					"<a href='" . $bsdriver->PathBase
						. $bsdriver->PlatformParm . 'sc_menu=' . $bsdriver->MenuName . $bsdriver->PageState
						. '&sc_action=sort&fld=' . $bsdriver->Field->id_by_name( $gvColumn->DbColumnName ) . $sortStatus
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
	 * Validate user input and insert row into database table.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	public function row_insert()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$bsdriver->trace_text( '<br />' );
		foreach ( $_POST as $k => $v ) {
			if ( strlen( $k ) > 4 && substr( $k, 0, 4 ) == 'ExFy' ) {
				$bsdriver->trace_text( ' [' . $k . ' ' . $v . ']' );
				$bsdriver->trace_text( '<br />' );
				$id = substr( $k, 4 );
				if ( !$this->is_valid_dbinteger( 'ID', $id ) ) {
					break;
				} else {
					$this->db_change(
						'P_ExtendUpdate',
						'( %d, %d, %d )',
						array( $bsuser->OrgID, $bsuser->ID, $id ),
						__( 'Fiscal Year extended on checked items' ),
						array()
				);
				}
			}
		}
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

		$C_ID         = $row[$bsdriver->Field->C_ID];
		$C_FY1Title   = $row[$bsdriver->Field->C_FY1Title];
		$C_FY2Title   = $row[$bsdriver->Field->C_FY2Title];
		$C_DeptTitle2 = $row[$bsdriver->Field->C_DeptTitle2];
		$C_Title      = $row[$bsdriver->Field->C_Title];


		return	ObjtrackerEasyStatic::table_td( '', "<input type='checkbox' id='ExFy" . $C_ID . "' name='ExFy" . $C_ID . "'>" )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '',  $C_FY1Title . ' ' . __( 'to' ) . ' ' . $C_FY2Title )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_DeptTitle2 ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Title ) );
	}
}

?>
