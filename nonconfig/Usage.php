<?php
/**
 * Lists people of a department or objectives of people, fiscal years, frequency, types, ...
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */
	/**
	 * Short Description
	 *
	 * Long Description
	 *
	 * @hook     action 'admin_notices'
	 * @since    1.0
	 * @param    type    $varname    Description
	 * @return   type                Description
	 * @return   void
	 */
/**
 * Returns this page unique html text.
 *
 * @since    1.0
 * @param    object  $bsdriver   The environment object
 * @param    object  $bsuser     The user object
 * @return   string              Page's unique html text.
 */
function bs_usage( $bsdriver, $bsuser )
{
	if ( isset( $_GET['Table'] ) ) {
		$table  = $_GET['Table'];
		$column = $_GET['Column'];
		$value  = $_GET['Value'];
	}
	if ( $table == 'T_Person' )
		$gridview1 = new BsUsagePage(
			$bsdriver,
			$bsuser,
			'This table lists all of the people in the selected department.',
			array(
				new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
				new ObjtrackerGvColumn( __( 'Objectives' ), 'C_Usage' ),
				new ObjtrackerGvColumn( __( 'Full Name' ), 'C_FullName' ),
				new ObjtrackerGvColumn( __( 'Logon ID' ), 'C_UserName' ),
				new ObjtrackerGvColumn( __( 'Department' ), 'C_Department' ),
				new ObjtrackerGvColumn( __( 'Admin' ), 'C_IsAdmin' ),
				new ObjtrackerGvColumn( __( 'Active' ), 'C_Active' ),
				new ObjtrackerGvColumn( __( 'Changed' ), 'C_Track_Changed' ),
				new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
			),
			'P_Usage',
			'( %d, %d, %s, %s, %s )',
			array( $bsuser->OrgID, $bsuser->ID, $table, $column, $value, ),
			'Dummy'
			);
	else
		$gridview1 = new BsUsagePage(
			$bsdriver,
			$bsuser,
			'Table referenced by field with value',
			array(
				new ObjtrackerGvColumn( __( 'ID' ), 'C_ID' ),
				new ObjtrackerGvColumn( __( 'Objective' ), 'C_Title' ),
				new ObjtrackerGvColumn( __( 'Department' ), 'C_Department' ),
				new ObjtrackerGvColumn( __( 'Owner' ), 'C_Owner' ),
				new ObjtrackerGvColumn( __( 'Changed' ), 'C_Track_Changed' ),
				new ObjtrackerGvColumn( __( 'By' ), 'C_Track_Userid' ),
			),
			'P_Usage',
			'( %d, %d, %s, %s, %s )',
			array( $bsuser->OrgID, $bsuser->ID, $table, $column, $value ),
			'Dummy'
			);

	return $gridview1->Response();
}
/**
 * Relate a field's value to other related database fields with the same value.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsUsagePage extends ObjtrackerConfigPage
{
	const PARM_TABLE  = 2;
	const PARM_COLUMN = 3;
	const PARM_VALUE  = 4;

	/**
	 * Return a footer row of a gridview.
	 *
	 * @since    1.0
	 * @returns  string      Html segment containing a row of a gridview
	 */
	function gridview_footer()
	{
		return '';
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
	 * Short Description
	 *
	 * Long Description
	 *
	 * @hook     action 'admin_notices'
	 * @param    type    $varname    Description
	 * @return   type                Description
	 * @return   void
	 */

	function preface()
	{
		$bsdriver = $this->bsdriver;


		$bsdriver->PageState = '&Table=' . $this->DbProcParms[self::PARM_TABLE] . '&Column=' . $this->DbProcParms[self::PARM_COLUMN]
				. '&Value=' . $this->DbProcParms[self::PARM_VALUE] ;

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

		$Results = $bsdriver->platform_db_query( 'P_UsageTitle', $this->DbProcArgs, $this->DbProcParms );
		if ( count( $Results ) == 1 ) {
			$row = $Results[0];

			if ( $this->DbProcParms[self::PARM_TABLE] == 'T_Person' )
				$comment = __( 'This table lists all of the people in the selected department.' );
			elseif ( $this->DbProcParms[self::PARM_COLUMN] == 'OwnerID' )
				$comment = __( 'This table lists all of the objectives for the selected owner.' );
			elseif ( $this->DbProcParms[self::PARM_COLUMN] == 'FiscalYear' )
				$comment = __( 'This table lists all of the objectives for the selected fiscal year.' );
			elseif ( $this->DbProcParms[self::PARM_COLUMN] == 'FrequencyID' )
				$comment = __( 'This table lists all of the objectives for the selected frequency.' );
			elseif ( $this->DbProcParms[self::PARM_COLUMN] == 'MetricTypeID' )
				$comment = __( 'This table lists all of the objectives for the selected metric.' );
			elseif ( $this->DbProcParms[self::PARM_COLUMN] == 'TypeID' )
				$comment = __( 'This table lists all of the objectives for the selected objective type.' );
			else
				$comment = __( 'Internal error.' );

			return
				__( 'Table' ) . '<b> ' . $row[$bsdriver->Field->C_TableName] . "</b> \n"
				. __( 'referenced by' ) . '<b> ' . $row[$bsdriver->Field->C_Column] . "</b> \n"
				. __( 'with value' ) . '<b> ' . $row[$bsdriver->Field->C_Value] . "</b> \n"
				. __( 'representing' ) . '<b> ' . $row[$bsdriver->Field->C_Representing] . '</b>.<br /><br />'
				. $comment . "<br /><br />\n"
				;
		} else {
			$this->ValidationError = $bsdriver->error_message( __( 'ERROR: items not found.' ) );
			return '';
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
		$this->description_text( __( 'The table below lists the departments of people who are assigned to objectives.' ) );

		$this->description_list_start();
		if ( $this->DbProcParms[self::PARM_TABLE] == 'T_Person' ) {
			$this->description_list_item(
				__(
					'To sort the table, click on <b>ID</b>, <b>Objectives</b>, <b>Full Name</b>, <b>Logon ID</b>,
					<b>Department</b>, <b>Admin</b>, <b>Active</b>, <b>Changed</b>, or <b>By</b>.'
					)
				);
		} else {
			$this->description_list_item(
				__(
					'To sort the table, click on <b>ID</b>, <b>Objective</b>, <b>Department</b>,
					<b>Owner</b>, <b>Changed</b>, or <b>By</b>.'
					)
				);
		}
		$this->description_list_end();
		$this->description_end();

		$this->_Description .= $bsdriver->title_heading( __( 'Usage' ) );

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

		if ( $this->DbProcParms[self::PARM_TABLE] == 'T_Person' ) {
			$C_ID            = $row[$bsdriver->Field->C_ID];
			$C_FullName      = $row[$bsdriver->Field->C_FullName];
			$C_UserName      = $row[$bsdriver->Field->C_UserName];
			$C_IsAdmin       = $row[$bsdriver->Field->C_IsAdmin];
			$C_IsActive      = $row[$bsdriver->Field->C_IsActive];
			$C_Department    = $row[$bsdriver->Field->C_Department];
			$C_Usage         = $row[$bsdriver->Field->C_Usage];
			$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
			$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

			// Make usage more readable and a bigger target for clicking
			$usageLink = ( $C_Usage == 0 )
				? __( 'None' )
				: "<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Usage&Table=T_Objective&Column=OwnerID&Value='
					. $C_ID . "'>" . __( 'Yes:' ) . $C_Usage . '</a>';

			return
				ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td( '', $usageLink )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_FullName ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Department ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_UserName ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_IsAdmin ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_IsActive ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) );
		} else {
			$C_ID            = $row[$bsdriver->Field->C_ID];
			$C_Title         = $row[$bsdriver->Field->C_Title];
			$C_Department    = $row[$bsdriver->Field->C_Department];
			$C_Person        = $row[$bsdriver->Field->C_Person];
			$C_Track_Changed = $row[$bsdriver->Field->C_Track_Changed];
			$C_Track_Userid  = $row[$bsdriver->Field->C_Track_Userid];

			return
				ObjtrackerEasyStatic::table_td( '', stripslashes( $C_ID ) )
				. ObjtrackerEasyStatic::table_td(
					'',
					"<a href='" . $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Objective&ID='
						. $C_ID . "'>" . $C_Title . '</a>'
					)
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Department ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Person ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Changed ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Track_Userid ) );
		}
	}
}

?>

