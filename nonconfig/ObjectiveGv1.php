<?php
/**
 * Assists Objective.php by managing display and updating of an objective's targets.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Objective's target processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsObjectiveGridView1 extends ObjtrackerConfigPage
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
	 * Holds object of the objective definition.
	 *
	 * @var BsObjective
	 * @access private
	 * @since 1.0
	 */
	public $Formview1;

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

		$this->MetricTypeID = $this->Formview1->MetricTypeID;

		$this->ActionChar = $bsdriver->Panel == '1' ? substr( $bsdriver->Action, 0, 1 ) : self::BSPAGE_LIST;
		$prefix           = $this->preface();
		$this->description();
		$prefix2 = $this->preface2();

		if ( $bsuser->should_show( ObjtrackerUser::UIShowTargets ) ) {
			$bsdriver->trace_text( '<br />obj1panel(' . $bsdriver->Panel . ' ' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );

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

			switch ( $this->ActionChar ) {
			case self::BSPAGE_ADD: // User press "Add" button
				$this->item_insert();
				break;
			case self::BSPAGE_EDIT: // User press "Edit" button
				break;
			case self::BSPAGE_UPD: // User press "Update" button
				$this->item_update();
				break;
			case self::BSPAGE_DEL: // User press "Delete" button
				$this->item_delete();
				break;
			case self::BSPAGE_SORT: // User click to sort by column
				$lastSort = $_GET[ 'Ss' ];
				if ( strlen( $lastSort ) > 1 ) {
					$lastField     = substr( $lastSort, 2 );
					$lastDirection = substr( lastSort, 0, 1 );

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
			$this->GvResults = $bsdriver->platform_db_query(
				$this->DbProcList,
				$this->DbProcArgs,
				$this->DbProcParms
				);
			$this->RowCount  = count( $this->GvResults );

			return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $bsdriver->platform_start_form( '&p=1&ID=' . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID], '' )
				. $prefix
				. $this->_Description
				. $prefix2
				. $this->ValidationMsg
				. $this->gridview1()
				. $bsdriver->EndForm;
		} else {
			return $this->_Description;
		}
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
			$bsdriver->trace_text( 'No sorting, HiddenSortInfo=' . $this->HiddenSortDirection . $this->HiddenSortField );
			$hiddenValues = "\n<input type='hidden' name='SortHiddenField' value='' />\n"
							. "<input type='hidden' name='SortHiddenDirection' value='' />\n";
			$sortUrl      = '&Ss=';
		}
		$hiddenValues .= "<input type='hidden' name='HiddenObjectiveID' value='" . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] . "' />\n";

		// Write each row of gridview
		$tableBody  = '';
		$primaryRow = true;

		foreach ( $this->GvResults as $row ) {
			$tableBody .= $primaryRow ? ObjtrackerEasyStatic::table_tr_start( "class='BssGvOddRow'" ) : ObjtrackerEasyStatic::table_tr_start( "class='BssGvEvenRow'" );

			$primaryRow   = $primaryRow ? false : true;
			$C_FiscalYear = $row[$bsdriver->Field->C_FiscalYear];

			if ( $this->ActionChar == self::BSPAGE_EDIT && $C_FiscalYear == $_GET['C_FiscalYear'] ) {
				$bsdriver->trace_text( ' showrow_edit_this' );
				$tableBody .= $this->showrow_edit_this( $row );
			} elseif ( $this->ActionChar == self::BSPAGE_UPD && $C_FiscalYear == $_POST['C_FiscalYear'] ) {
					$tableBody .= $this->showrow_update_this( $row );
			} elseif ( $this->ActionChar == self::BSPAGE_EDIT || $this->ActionChar == self::BSPAGE_UPD ) {
				$tableBody .= $this->showrow_other( $row );
			} else {
				$tableBody .= $this->showrow_list( $row );
			}
			$tableBody .= ObjtrackerEasyStatic::table_tr_end();
		}

		return	$hiddenValues
				. ObjtrackerEasyStatic::table_start( "style='width:650px;' class='BssGridview'" )
				. $bsdriver->sort_headers( $sortUrl, $this->GvColumns )
				. $tableBody
				. ObjtrackerEasyStatic::table_end();
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

		$bsdriver->PageState = '&p=1&ID=' . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID];

		$this->setpage_description_head( __( 'Fiscal Year Targets' ), ObjtrackerUser::UIShowTargets, '' );
		if ( $bsuser->should_show( ObjtrackerUser::UIShowTargets ) ) {
			$this->description_text( __( 'To change the target values for a fiscal year, click on <b>Edit</b>, change the value(s), and click on <b>Update</b>.' ) );
		}
		$this->description_end();
	}

	/**
	 * Validate the user input and update the targets.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function item_update()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_FiscalYear    = $_POST['C_FiscalYear'];
		$this->UserInput = new ObjtrackerFormField();
		$validator       = new ObjtrackerValidateTargets(
						$this->MetricTypeID,
						trim( $_POST['C_Target'] ),
						trim( $_POST['C_Target1'] ),
						trim( $_POST['C_Target2'] )
						);

		// Retrieved normalized strings:n
		$this->UserInput->C_Target  = $validator->normalized_target();
		$this->UserInput->C_Target1 = $validator->normalized_target1();
		$this->UserInput->C_Target2 = $validator->normalized_target2();

		if ( strlen( $validator->error() ) ) {
			$this->ValidationMsg = $bsdriver->error_message( $validator->error() );
			$this->UserError     = true;
		} elseif ( !$this->is_valid_dbinteger( 'ID', $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] ) ) {
		} else {
			$this->db_change(
					'P_TargetObjUpdate',
					'( %d, %d, %d, %d, %s, %s, %s )',
					array(
						$bsuser->OrgID, $bsuser->ID,
						$this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID],
						$C_FiscalYear,
						$this->UserInput->C_Target,
						$this->UserInput->C_Target1,
						$this->UserInput->C_Target2,
						),
					'',
					array()
				);
		}
	}


	/**
	 * Validate the user input and delete the targets.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function item_delete()
	{
		return '';
	}

	/**
	 * Return a row of a gridview that user click edit on.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function showrow_edit_this( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_FiscalYear      = $row[$bsdriver->Field->C_FiscalYear];
		$C_FiscalYearTitle = $row[$bsdriver->Field->C_FiscalYearTitle];
		$C_Target          = $row[$bsdriver->Field->C_Target];
		$C_Target1         = $row[$bsdriver->Field->C_Target1];
		$C_Target2         = $row[$bsdriver->Field->C_Target2];
		$C_Track_Changed   = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid    = $row[$bsdriver->Field->C_Track_Userid];

		$cancelButton = $bsdriver->Input1SubmitCancel;

		return	'<td>' . stripslashes( $C_FiscalYearTitle ) . "</td>\n"
				. "<input type='hidden' name='ID' value='" . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] . "' />"
				. "<input type='hidden' name='C_FiscalYear' value='" . $C_FiscalYear . "' />"
				. "<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' /></td>\n"
				. "<td><input type='text' size='16' name='C_Target' value='" . $C_Target . "' /></td> \n"
				. "<td><input type='text' size='16' name='C_Target1' value='" . $C_Target1 . "' /></td> \n"
				. "<td><input type='text' size='16' name='C_Target2' value='" . $C_Target2 . "' /></td> \n"
				. '<td>' . stripslashes( $C_Track_Changed ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Userid ) . "</td>\n"
				. "<td>\n"
				. $bsdriver->Input1SubmitUpdate
				. $cancelButton
				. "</td>\n";
	}

	/**
	 * Return display/update form for an objective's target.
	 *
	 * @since    1.0
	 * @params   array	$row        Row array of data
	 * @returns  string             Html segment containing display/edit form.
	 */
	function showrow_update_this( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_FiscalYear      = $row[$bsdriver->Field->C_FiscalYear];
		$C_FiscalYearTitle = $row[$bsdriver->Field->C_FiscalYearTitle];
		$C_Track_Changed   = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid    = $row[$bsdriver->Field->C_Track_Userid];

		$cancelButton = $bsdriver->Input1SubmitCancel;
		return	'<td>' . stripslashes( $C_FiscalYearTitle ) . "</td>\n"
				. "<input type='hidden' name='ID' value='" . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] . "' />"
				. "<input type='hidden' name='C_FiscalYear' value='" . $C_FiscalYear . "' />"
				. "<input type='hidden' name='sc_menu' value='" . $bsdriver->MenuName . "' /></td>\n"
				. "<td><input type='text' size='16' name='C_Target' value='" . $this->UserInput->C_Target . "' /></td> \n"
				. "<td><input type='text' size='16' name='C_Target1' value='" . $this->UserInput->C_Target1 . "' /></td> \n"
				. "<td><input type='text' size='16' name='C_Target2' value='" . $this->UserInput->C_Target2 . "' /></td> \n"
				. '<td>' . stripslashes( $C_Track_Changed ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Userid ) . "</td>\n"
				. '<td>'
				. $bsdriver->Input1SubmitUpdate
				. $cancelButton
				. "</td>\n";
	}

	/**
	 * Return display/update row for a row not being edited.
	 *
	 * @since    1.0
	 * @params   array	$row        Row array of data
	 * @returns  string             Html segment containing display/edit form.
	 */
	function showrow_other( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_FiscalYear      = $row[$bsdriver->Field->C_FiscalYear];
		$C_FiscalYearTitle = $row[$bsdriver->Field->C_FiscalYearTitle];
		$C_Target          = $row[$bsdriver->Field->C_Target];
		$C_Target1         = $row[$bsdriver->Field->C_Target1];
		$C_Target2         = $row[$bsdriver->Field->C_Target2];
		$C_Track_Changed   = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid    = $row[$bsdriver->Field->C_Track_Userid];

		return	 '<td>' . stripslashes( $C_FiscalYearTitle ) . "</td>\n"
				. "<td style='text-align:right;'>" . stripslashes( $C_Target ) . "</td>\n"
				. "<td style='text-align:right;'>" . stripslashes( $C_Target1 ) . "</td>\n"
				. "<td style='text-align:right;'>" . stripslashes( $C_Target2 ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Changed ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Userid ) . "</td>\n"
				. "<td>&nbsp;</td>\n";
	}

	/**
	 * Return row when the item only being listed.
	 *
	 * @since    1.0
	 * @params   array	$row        Row array of data
	 * @returns  string             Html segment containing display/edit form.
	 */
	function showrow_list( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$C_FiscalYear      = $row[$bsdriver->Field->C_FiscalYear];
		$C_FiscalYearTitle = $row[$bsdriver->Field->C_FiscalYearTitle];
		$C_Target          = $row[$bsdriver->Field->C_Target];
		$C_Target1         = $row[$bsdriver->Field->C_Target1];
		$C_Target2         = $row[$bsdriver->Field->C_Target2];
		$C_Track_Changed   = $row[$bsdriver->Field->C_Track_Changed];
		$C_Track_Userid    = $row[$bsdriver->Field->C_Track_Userid];

		// Only admins can edit
		$editButton = ( !$bsuser->is_admin() )
			? ''
			: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&p=1&sc_action=edit&ID=' . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID]
				. '&C_FiscalYear=' . $C_FiscalYear . "'>" . __( 'Edit' ) . '</a>';

		return	'<td>' . stripslashes( $C_FiscalYearTitle ) . "</td>\n"
				. "<td style='text-align:right;'>" . stripslashes( $C_Target ) . "</td>\n"
				. "<td style='text-align:right;'>" . stripslashes( $C_Target1 ) . "</td>\n"
				. "<td style='text-align:right;'>" . stripslashes( $C_Target2 ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Changed ) . "</td>\n"
				. '<td>' . stripslashes( $C_Track_Userid ) . "</td>\n"
				. '<td>' . $editButton . "</td>\n";
	}
}

?>
