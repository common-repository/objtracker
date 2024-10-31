<?php
/**
 * File constains a class with static methods.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Contains static methods.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerEasyStatic
{
	/**
	 * Dummy fiscal year that indicates that the first month of fiscal year hasn't been set.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	const DummyFiscalYear = '1949';

	/**
	 * Format table tag.
	 *
	 * @since    1.0
	 * @param    string $attributes    Whatever goes into a table tag.
	 * @return   string                Sql Fragment.
	 */
	public static function table_start( $attributes )
	{
		if ( strlen( $attributes ) == 0 ) {
			return "<table>\n";
		} else {
			return '<table ' . $attributes . " >\n";
		}
	}

	/**
	 * Format end table tag.
	 *
	 * @since    1.0
	 * @return   string                Sql Fragment.
	 */
	public static function table_end()
	{
		return "</table>\n";
	}

	/**
	 * Format tr table tag.
	 *
	 * @since    1.0
	 * @param    string $attributes    Whatever goes into a tr tag.
	 * @return   string                Sql Fragment.
	 */
	public static function table_tr_start( $attributes )
	{
		if ( strlen( $attributes ) == 0 ) {
			return "<tr>\n";
		} else {
			return '<tr ' . $attributes . " >\n";
		}
	}

	/**
	 * Format end tr table tag.
	 *
	 * @since    1.0
	 * @return   string                Sql Fragment.
	 */
	public static function table_tr_end()
	{
		return "</tr>\n";
	}

	/**
	 * Format td table tag.
	 *
	 * @since    1.0
	 * @param    string $attributes    Whatever goes into a td tag.
	 * @param    string $text          Whatever goes between start and end of tags.
	 * @return   string                Sql Fragment.
	 */
	public static function table_td( $attributes, $text )
	{
		if ( strlen( $attributes ) == 0 ) {
			return '<td>' . $text . "</td>\n";
		} else {
			return '<td ' . $attributes . ' >' . $text . "</td>\n";
		}
	}

	/**
	 * Format th table tag.
	 *
	 * @since    1.0
	 * @param    string $attributes    Whatever goes into a th tag.
	 * @param    string $text          Whatever goes between start and end of tags.
	 * @return   string                Sql Fragment.
	 */
	public static function table_th( $attributes, $text )
	{
		if ( strlen( $attributes ) == 0 ) {
			return '<th>' . $text . "</th>\n";
		} else {
			return '<th ' . $attributes . ' >' . $text . "</th>\n";
		}
	}
	/**
	 * Format top level menu item.
	 *
	 * @since    1.0
	 * @param    string $bsdriver     Environment object.
	 * @param    string $name         name of link.
	 * @param    string $parms        parameters of link.
	 * @return   string               Sql Fragment.
	 */
	public static function menu_tdtabletrth( $bsdriver, $name, $parms )
	{
		return "<td><table><tr><th><a href='" . $bsdriver->PlatformParm . 'sc_menu=' . $parms . "'>" . $name . "</a></th></tr></table></td>\n";
	}

	/**
	 * Format lower level menu item.
	 *
	 * @since    1.0
	 * @param    string $bsdriver     Environment object.
	 * @param    string $name         name of link.
	 * @param    string $parms        parameters of link.
	 * @return   string               Sql Fragment.
	 */
	public static function menu_trtd( $bsdriver, $name, $parms )
	{
		return "<tr><td><a href='" . $bsdriver->PlatformParm . 'sc_menu=' . $parms . "'>" . $name . "</a></td></tr>\n";
	}

	/**
	 * Format beginning of fiscal year for provided date.
	 *
	 * @since    1.0
	 * @param    int    $firstMonth    First month of the fiscal year.
	 * @param    string $dt            Date whose fiscal year is needed.
	 * @return   string                Beginning of fiscal year for provided date.
	 */
	public static function first_fy_year_bydate( $firstMonth, $dt )
	{
		$month = substr( $dt, 5, 2 );
		$year  = substr( $dt, 0, 4 );
		if ( $month < $firstMonth )
			$year--;
		return sprintf( '%.4d-%.2d-01', $year, $firstMonth );
	}

	/**
	 * Get image name from status name.
	 *
	 * @since    1.0
	 * @param    string    $status    Status name.
	 * @return   string               Image name.
	 */
	public static function get_statusurl( $status )
	{
		return 'Status' . $status . '.png';
	}

	/**
	 * Get image's hover text from the status name.
	 *
	 * @since    1.0
	 * @param    string    $status    Status name.
	 * @return   string               Image name.
	 */
	public static function hovertext_by_status( $status )
	{
		if ( $status == 'COMPLETE' )
			return __( 'The measurement matches or exceeds assigned target.' );
		elseif ( $status == 'GREEN' )
			return __( 'The measurement matches or exceeds the near target but is short of the target.' );
		elseif ( $status == 'YELLOW' )
			return __( 'The measurement matches or exceeds the far target but is short of the near target.' );
		elseif ( $status == 'RED' )
			return __( 'The measurement is far from the target.' );
		elseif ( $status == 'LATE' )
			return __( 'No measurement has been entered.' );
		else
			return __( "Your missing measurement has been pre-populated with a value of 'Missing'." );
	}
}
?>
