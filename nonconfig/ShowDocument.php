<?php
/**
 * Reads attached documents and downloads to browser.
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
function bs_showdocument( $bsdriver, $bsuser )
{
	if ( isset( $_GET['ID']) ) {
		$id = $_GET['ID'];
		if ( !$bsdriver->platform_test_token( $_GET['token'], $id ) ) {
			return '<html>' . __( 'Security error.' ) . '</html>';
		}

		$results = $bsdriver->platform_db_query(
						'P_Documentation',
						'( %d, %d, %d)',
						array( $bsuser->OrgID, $bsuser->ID, $id )
						);
		$row     = $results[0];

		$C_Filename       = $row[$bsdriver->Field->C_Filename];
		$C_MimeType       = $row[$bsdriver->Field->C_MimeType];
		$C_ObjectiveID    = $row[$bsdriver->Field->C_ObjectiveID];
		$C_PeriodStarting = substr( $row[$bsdriver->Field->C_PeriodStarting], 0, 10 );

		$file = $bsuser->UploadFsPath . '\\'
					. $C_ObjectiveID . '-'
					. $C_PeriodStarting . '-'
					. str_pad( $id, 3, '0', STR_PAD_LEFT ) . '-'
					. $C_Filename;

		if ( !file_exists( $file ) )
			return '<html>' . __( 'Error: File does not exist.' ) . '</html>';
	} elseif ( isset( $_GET['doc'] ) ) {
		$file = $bsdriver->AbsolutePathHelp . '/' . $_GET['doc'] ;

//		$bsdriver->trace_text( ' entry=' . $file );
		if ( !file_exists( $file ) ) {
			return '<html>' . __( 'Error: Document does not exist.' ) . ' ' . $file . '</html>';
		}
	} else {
		return '<html>' . __( 'Error: Incorrect parms' ) . '</html>';
	}
//		$bsdriver->trace_text( ' found=' . $file );

	// For Drupal, write the httpheaders
	$bsdriver->write_httpheaders();

	# installing at the toplevel
	$my_default_level = ob_get_level(); # learn about already set output buffers
	$my_has_buffer    = ob_start(); # my output buffer, with flagging
	ob_clean();
	flush();

	readfile( $file );
	die();
}


?>
