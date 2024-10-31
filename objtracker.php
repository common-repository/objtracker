<?php
/*
Plugin Name: objtracker
Plugin URI: http://bsdanroller.wordpress.com/
Description: Set objectives and collect data on how well you have succeeded in meeting those objectives. 
Version: 1.0.7
Author: Dan Roller
Author URI: http://bsdanroller.wordpress.com/
*/

/**
 * Lead module for WordPress using admin and showing WordPress menus.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

// ini_set( 'display_errors', 'On' );
// error_reporting( E_ALL | E_STRICT );


add_action( 'init', 'objtracker_init' );
add_action( 'admin_menu', 'objtracker_toolmenus' );
/**
 * Set tool menu for plugin
 *
 * @hook     action 'admin_menu'
 * @since    1.0
 * @return   void
 */
function objtracker_init()
{
	if ( isset( $_GET['mimetype'] ) ) {
		objtracker_define_include();

		$basename = 'objtracker';
		$bsdriver = new ObjtrackerWordPress( ObjtrackerDriver::BsTemplateDownload, $basename );
		$bsuser   = $bsdriver->Platform_User_Get();

		if ( $bsuser->Error != '' ) {
			header( 'Content-Type: text/html' );
			header( 'Cache-control: private' );
			header( 'Pragma: private' );
			header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );

			return 
				"<!DOCTYPE html>\n"
				. "<html xmlns='http://www.w3.org/1999/xhtml'>\n"
				. "<head>\n"
				. "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />\n"
				. '<title>' . $bsdriver->PlatformModule . "on WordPress</title>\n"
				. '</head><body>'
				. $bsuser->Error
				. "</body></html>\n";
		}

		$task = $_GET['sc_menu'];
		include 'nonconfig/' . $task . '.php';

		// Call module's entry function
		call_user_func(
			'bs_' . strtolower( $task ),	// function name
			$bsdriver,		// Parm: 1 - Environmentals for WordPress, Drupal, ...
			$bsuser			// Parm: 2 - The user object
		);
	}
}
/**
 * Set tool menu for plugin
 *
 * @hook     action 'admin_menu'
 * @since    1.0
 * @return   void
 */
function objtracker_toolmenus()
{
	if ( current_user_can( 'objtrackeradmin' ) ) {
		add_management_page( __( 'objtracker' ), __( 'objtracker' ), 'objtrackeradmin', 'objtracker/objtracker.php', 'objtracker_main' );
	} else {
		add_management_page( __( 'objtracker' ), __( 'objtracker' ), 'objtracker', 'objtracker/objtracker.php', 'objtracker_main' );
	}
}

/**
 * Outputs the main administration screen, and handles installing/upgrading, saving, and deleting.
 *
 * Long Description
 *
 * @hook     action 'add_menu_page'
 * @since    1.0
 * @return   void
 */
function objtracker_main()
{
	objtracker_define_include();

	$basename = 'objtracker';
	$bsdriver = new ObjtrackerWordPress( ObjtrackerDriver::BsTemplateYes, $basename );
	$bsuser   = $bsdriver->Platform_User_Get();
	if ( $bsuser->Error != '' ) {
		wp_register_style( 'objtrackercss', plugins_url( '/css/objtracker.css', __FILE__ ) );
		wp_enqueue_style( 'objtrackercss' );
		echo ' ' . $bsuser->Error;
	} else {
		wp_register_style( 'objtrackercss', plugins_url( '/css/objtracker.css', __FILE__ ) );
		wp_enqueue_style( 'objtrackercss' );
		wp_register_style( 'objtrackermenucss', plugins_url( '/menu_script/Menu.css', __FILE__ ) );
		wp_enqueue_style( 'objtrackermenucss' );
		wp_enqueue_script( 'objtrackerjs', plugins_url( '/js/objtracker.js', __FILE__ ) );
		wp_enqueue_script( 'objtrackermenujs', plugins_url( '/menu_script/DHTMLMenuExpanderV2.js', __FILE__ ) );
		echo ' ' . $bsdriver->balanced_scorecard( $bsuser, '' );
	}
}
/**
 * Define required values
 *
 * @since    1.0.4
 * @return   void
 */
function objtracker_define_include()
{
define( 'OBJTRACKER_CONTENT_DIR', WP_CONTENT_DIR . '/plugins/objtracker/' );
define( 'OBJTRACKER_PRIMARY', 'Original' );

define( 'OBJTRACKER_CONTENT_MTYPE', '.php' );

include OBJTRACKER_CONTENT_DIR . 'includes/class-driver' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_CONTENT_DIR . 'includes/class-user' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_CONTENT_DIR . 'includes/dataset' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_CONTENT_DIR . 'includes/class-page' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_CONTENT_DIR . 'includes/class-gvcolumn' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_CONTENT_DIR . 'includes/class-measuredobjective' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_CONTENT_DIR . 'includes/class-easystatic' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_CONTENT_DIR . 'includes/class-metrics' . OBJTRACKER_CONTENT_MTYPE;
include OBJTRACKER_CONTENT_DIR . 'includes/class-wordpress' . OBJTRACKER_CONTENT_MTYPE;

define( 'OBJTRACKER_INC2INC_DIR', 'includes/' ); 	// include   module includes include   module
define( 'OBJTRACKER_NON2NON_DIR', '' ); 			// nonconfig module includes nonconfig module
define( 'OBJTRACKER_NON2INC_DIR', OBJTRACKER_CONTENT_DIR . 'includes/' ); 	// nonconfig module includes include   module

}
?>
