<?php
/**
 * Document the plugin's tables and fields.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Document the plugins tables and fields.
 *
 *	 Requirements:
 *		o Only admins can update
 *		o Names of frequency must be unique, stored proc checks
 *		o Link to list of objectives that match
 *		o Sortable column headers
 *
 * @since    1.0
 * @param    object  $bsdriver   The environment object
 * @param    object  $bsuser     The user object
 * @return   string              Page's unique html text.
 */
function bs_tableinfo( $bsdriver, $bsuser )
{
	$table = isset( $_GET['Table'] )
		? $_GET['Table']
		: $bsdriver->DbPrefix . 'T_Audit';

	$configPage = new BsTableInfoPage(
		$bsdriver,
		$bsuser,
		__( "Table's Columns" ),	// Parm: Title
		array(				// Parm: Gridview columns
				new ObjtrackerGvColumn( __( 'Column Name' ), 'C_ID' ),
				new ObjtrackerGvColumn( __( 'Name' ), 'C_Description' ),
				new ObjtrackerGvColumn( __( 'Description' ), 'C_Description' ),
			),
		'P_Audit_DocumentColumn',
		'( %d, %d, %s )',
		array( $bsuser->OrgID, $bsuser->ID, $table ),
		'' //delete msg does not occur
		);
	return $configPage->Response();
}

/**
 * Table info processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsTableInfoPage extends ObjtrackerConfigPage
{
	const PARM_TABLE = 2;

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

		$this->setpage_description_head( __( 'Description' ), ObjtrackerUser::UIShowInfo, '' );
		$this->description_text( __( "This page describes the Balanced Scorecard's database tables and their columns." ) );
		$this->description_end();
	}

	/**
	 * Fill in area between description an grid view.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function preface2()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		$selectedTable   = $this->DbProcParms[self::PARM_TABLE];
		$this->PageState = '&Table=' . $selectedTable;

		$results = $bsdriver->platform_db_query(
			'P_Audit_DocumentTables',
			'( %d, %d )',
			array( $bsuser->OrgID, $bsuser->ID )
			);

		$txt = $bsdriver->description_headertext( __( "Table's Description" ) );

		$txt .= "&nbsp;&nbsp;&nbsp;<select name='C_TableName' id='C_TableName' onchange=\"BsOnChange('"
			. $bsdriver->PathBase . $bsdriver->PlatformParm . 'sc_menu=Admin-TableInfo&Table='
			. "',this.form.C_TableName,'','' );\" >\n";

		foreach ( $results as $row ) {
			if ( $selectedTable == $row[$bsdriver->Field->C_TableName] ) {
				$txt .= " <option value='" . $row[$bsdriver->Field->C_TableName]
						. "' selected='selected'>" . $row[$bsdriver->Field->C_Description] . " </option>\n";
			} else {
				$txt .= " <option value='" . $row[$bsdriver->Field->C_TableName]
						. "'>" . $row[$bsdriver->Field->C_Description] . " </option>\n";
			}
		}

		$txt .= '</select>';

		$results = $bsdriver->platform_db_query( 'P_Audit_DocumentTable', $this->DbProcArgs, $this->DbProcParms );
		$row     = $results[0];

		$formview = ObjtrackerEasyStatic::table_start( "class='BssFormview'" )
			. ObjtrackerEasyStatic::table_td( "style='width: 15%;'", __( 'Table Name' ) )
			. ObjtrackerEasyStatic::table_td( "style='font-weight: bold'", $row[$bsdriver->Field->C_TName] )
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td( '', __( 'Name' ) )
			. ObjtrackerEasyStatic::table_td( "style='font-weight: bold'", $row[$bsdriver->Field->C_Description] )
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td( '', __( 'Description' ) )
			. ObjtrackerEasyStatic::table_td( "style='font-weight: bold'", $row[$bsdriver->Field->C_Documentation] )
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_end()
			. "<br />\n";

		$formview .= $bsdriver->description_headertext( __( "Table's Columns" ) );
		return $txt . $formview ;
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
	 * Validate user input and insert row into database table.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_insert()
	{
		return '';
	}

	/**
	 * Validate user input and update a row in database table.
	 *
	 * @since    1.0
	 * @returns  void
	 */
	function row_update()
	{
		return '';
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
		return '';
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

		$C_CName         = $row[$bsdriver->Field->C_CName];
		$C_Description   = $row[$bsdriver->Field->C_Description];
		$C_Documentation = $row[$bsdriver->Field->C_Documentation];

		return	ObjtrackerEasyStatic::table_td( '', stripslashes( $C_CName ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Description ) )
				. ObjtrackerEasyStatic::table_td( '', stripslashes( $C_Documentation ) );
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
}

?>
