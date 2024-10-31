<?php
/**
 * Miscellaneous functions living outside of any class.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

	/**
	 * Compare two items while sorting ascending.
	 *
	 * @since    1.0
	 * @params   $a           Field of row n to compare
	 * @params   $b           Field of row m to compare
	 * @returns  int          Value indicating which is lower in sort sequence.
	 */
	function objtracker_dataset_sort_asc( $a, $b )
	{
		global $__dataset_sortcolumn;
		$al = strtolower( $a[$__dataset_sortcolumn] );
		$bl = strtolower( $b[$__dataset_sortcolumn] );
		if ( $al == $bl ) {
			return 0;
		}
		return ( $al > $bl ) ? 1 : -1;
	}

	/**
	 * Compare two items while sorting descending.
	 *
	 * @since    1.0
	 * @params   $a           Field of row n to compare
	 * @params   $b           Field of row m to compare
	 * @returns  int          Value indicating which is lower in sort sequence.
	 */
	function objtracker_dataset_sort_desc( $a, $b )
	{
		global $__dataset_sortcolumn;
		$al = strtolower( $a[$__dataset_sortcolumn] );
		$bl = strtolower( $b[$__dataset_sortcolumn] );
		if ( $al == $bl ) {
			return 0;
		}
		return ( $al < $bl ) ? 1 : -1;
	}

	/**
	 * Sort a database result by column.
	 *
	 * @since    1.0
	 * @params   $rows         Input array
	 * @params   $sc           Field number to sort
	 * @params   $sd           Sort direction (A or D)
	 * @returns  array         Sorted array.
	 */
	function objtracker_dataset_sort( $rows, $sc, $sd )
	{
		global $__dataset_sortcolumn;
		$__dataset_sortcolumn = $sc;
		if ( $sd == 'A' )
			usort( $rows, 'objtracker_dataset_sort_asc' );
		else
			usort( $rows, 'objtracker_dataset_sort_desc' );
		return $rows;
	}

?>
