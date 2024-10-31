<?php
/**
 * Assists Objective.php by managing display and updating of an objective's missing measurements.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Objective's missing measurement processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsObjectiveGridView3 extends ObjtrackerConfigPage
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
	 * Explain now to use page by updating class variable.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function description()
	{
		$this->_Description = '';
	}

	/**
	 * Return empty string to turn off editing of missing values..
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function showrow_edit_this( $row )
	{
		return '';
	}


	/**
	 * Return empty string to turn off editing of missing values..
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function showrow_update_this( $row )
	{
		return '';
	}

	/**
	 * Return empty string to turn off editing of missing values..
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function showrow_other( $row )
	{
		return '';
	}


	/**
	 * List the missing measurement periods.
	 *
	 * @since    1.0
	 * @params   $row        Row array of data
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function showrow_list( $row )
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		// Only admins can edit
		if ( !$bsuser->is_admin() )
			return '';

		$C_PeriodSorted   = $row[$bsdriver->Field->C_PeriodSorted];
		$C_PeriodStarting = substr( $row[$bsdriver->Field->C_PeriodStarting], 0, 10 );

		// Only admins can delete when row isn't only or in use
		$token     = $bsdriver->platform_get_token( $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID] );
		$addButton = "&nbsp;<a id='AddLinkButton_1' href='"
				. $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu='
				. $bsdriver->MenuName . '&p=2&sc_action=add&token=' . $token . '&ID=' . $this->DbProcParms[BsObjectivePage::PARM_OBJECTIVEID]
				. '&ID2=' . $C_PeriodSorted . "'>" . 'Add' . '</a>';

		return	ObjtrackerEasyStatic::table_td( '', $addButton )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_PeriodStarting ) );
	}

	/**
	 * List the missing measurement periods.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing a row of a gridview
	 */
	public function missing_response()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$message = '';
		$this->ActionChar = $bsdriver->Panel == '2' ? substr( $bsdriver->Action, 0, 1 ) : self::BSPAGE_LIST;
		$bsdriver->trace_text( '<br />obj3panel(' . $bsdriver->Panel . ' ' . $bsdriver->Action . ' ' . $this->ActionChar . ')' );


		$this->GvResults = $bsdriver->platform_db_query( $this->DbProcList, $this->DbProcArgs, $this->DbProcParms );
		$this->RowCount  = count( $this->GvResults );
		if ( $this->RowCount == 0 )
			return '&nbsp;';
		else
			return
				"\n<!-- xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx -->\n"
				. $this->gridview1();
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

		// Write each row of gridview
		$tableBody  = '';
		$primaryRow = true;
		foreach ( $this->GvResults as $row ) {
			$tableBody .= $primaryRow ? ObjtrackerEasyStatic::table_tr_start( "class='BssGvOddRow'" ) : ObjtrackerEasyStatic::table_tr_start( "class='BssGvEvenRow'" );
			$primaryRow = $primaryRow ? false : true;

			$tableBody .= $this->showrow_list( $row );
			$tableBody .= ObjtrackerEasyStatic::table_tr_end();
		}

		return
				"Click <b>Add</b> to add missing.<br />\n"
				. ObjtrackerEasyStatic::table_start( "class='BssGridview'" )
				. ObjtrackerEasyStatic::table_tr_start( '' ) 
				. ObjtrackerEasyStatic::table_td( '', '&nbsp' )
				. ObjtrackerEasyStatic::table_td( '', 'Period' )
				. ObjtrackerEasyStatic::table_tr_end()
				. $tableBody
				. ObjtrackerEasyStatic::table_end();
	}
}

?>
