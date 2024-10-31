<?php
/**
 * Class for holding a measured result.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Structure that assists in filtering measurements.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerMeasuredObjective
{
	/**
	 * Title of objective from request for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sTitle;

	/**
	 * Objectives's database id.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sOID;

	/**
	 * Measurement's database id.
	 *
	 * @var int
	 * @access public
	 * @since 1.0
	 */
	public $sMID;

	/**
	 * Measurement's status in comparison to  the fiscal year targets.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sStatus;

	/**
	 * 'Add' or 'Revise' indicating if measurement already exists.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sAction;

	/**
	 * Objective's objective type for measured data.
	 *
	 * @var char
	 * @access public
	 * @since 1.0
	 */
	public $sType;

	/**
	 * Measurements begin date.
	 *
	 * @var char
	 * @access public
	 * @since 1.0
	 */
	public $dPeriodStarting;

	/**
	 * Objective's frequency ID for measured data.
	 *
	 * @var char
	 * @access public
	 * @since 1.0
	 */
	public $cFrequencyID;

	/**
	 * Objective's metric type ID for measured data.
	 *
	 * @var char
	 * @access public
	 * @since 1.0
	 */
	public $cMetricTypeID;

	/**
	 * Objective's description for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sDescription;

	/**
	 * Objective's short department name for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sDeptTitle2;

	/**
	 * Objective's display frequency text for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sFrequency;

	/**
	 * Objective's fiscal year 1 for measured data.
	 *
	 * @var int
	 * @access public
	 * @since 1.0
	 */
	public $sFiscalYear1;

	/**
	 * Objective's fiscal year 2 for measured data.
	 *
	 * @var int
	 * @access public
	 * @since 1.0
	 */
	public $sFiscalYear2;

	/**
	 * Target value associated with measurement period for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sTarget;

	/**
	 * Target value associated with measurement period for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sFyTarget;

	/**
	 * Target value 1 (yellow threshold) associated with measurement period for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sTarget1;

	/**
	 * Target value 2 (red threshold) associated with measurement period for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sTarget2;

	/**
	 * Measurement value for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sMeasurement;
	/**
	 * Notes value for measured data.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sNotes;

//	public $sAlertPeriod;
//	public $dtAlertPeriod;
//	public $iAlertsPending;
//	public $sAlertsPending;

	/**
	 * Image hover string for alert status.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $sAlertReason;
}
?>
