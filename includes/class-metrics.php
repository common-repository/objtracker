<?php
/**
 * Class to manage validation and comparison of both targets and metrics.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Plugin class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerMetricValue
{
	/**
	 * Holds the metric object for the metric type.
	 *
	 * Each of the (very) few metric type here require a row in the
	 * T_MetricType table.
	 *
	 * @var ObjtrackerMetricValue
	 * @access private
	 * @since 1.0
	 */
	protected $oMetricObject;

	/**
	 * Constructor for ObjtrackerMetricValue
	 *
	 * @since    1.0
	 * @param    char      $sType    Type of metric
	 * @param    string    $sValue   Value of metric
	 * @return   void
	 */
	public function __construct( $sType, $sValue )
	{
		switch ( $sType ) {
		case 'P':
			$this->oMetricObject = new ObjtrackerMetricPercent( Trim( $sValue ) );
			break;
		case 'I':
			$this->oMetricObject = new ObjtrackerMetricInteger( Trim( $sValue ) );
			break;
		case 'R':
			$this->oMetricObject = new ObjtrackerMetricRatio( Trim( $sValue ) );
			break;
		case 'D':
			$this->oMetricObject = new ObjtrackerMetricDate( Trim( $sValue ) );
			break;
		case '$':
			$this->oMetricObject = new ObjtrackerMetricDollar( Trim( $sValue ) );
			break;
		default:
			$this->oMetricObject = new ObjtrackerMetricTypeUnknown( $sType, Trim( $sValue ) );
			break;
		}
	}

	/**
	 * Retrieve message if metric is incorrectly formed.
	 *
	 * @since    1.0
	 * @return   string                Message if metric is incorrectly formed.
	 */
	public function error()
	{
		return $this->oMetricObject->error();
	}

	/**
	 * Retrieve normalized value of original value.
	 *
	 * By normalized, 000 is changed to 0, $4444,10 changed to $444,410, etc.
	 * @since    1.0
	 * @return   string                The normalized value.
	 */
	public function normalized()
	{
		return $this->oMetricObject->normalized();
	}

	/**
	 * Retrieve original value.
	 *
	 * @since    1.0
	 * @return   string                The orginial value.
	 */
	public function value()
	{
		return $this->oMetricObject->value();
	}
}
/**
 * Class provides one interface for all metric types.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerMetricBase
{
	/**
	 * Holds input value to be judged.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $sRawValue;

	/**
	 * Holds input length of the metric value on entry.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	protected $sRawLength;

	/**
	 * Holds cleaned-up display value of the metric with reasonable punctuation.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $sReformated;

	/**
	 * Either '' or an error message.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $sError = '';

	/**
	 * Holds relative value of the metric for comparisons.
	 *
	 * @var int
	 * @access private
	 * @since 1.0
	 */
	protected $fValue = 0;

	/**
	 * Retrieve message if metric is incorrectly formed.
	 *
	 * @since    1.0
	 * @return   string                Message if metric is incorrectly formed.
	 */
	public function error()
	{
		return $this->sError;
	}

	/**
	 * Retrieve normalized value of original value.
	 *
	 *
	 * By normalized, 000 is changed to 0, $4444,10 changed to $444,410, etc.
	 * @since    1.0
	 * @return   string                The normalized value.
	 */
	public function normalized()
	{
		return $this->sReformated;
	}

	/**
	 * Retrieve original value.
	 *
	 * @since    1.0
	 * @return   string                The orginial value.
	 */
	public function value()
	{
		return $this->fValue;
	}

	/**
	 * Validate digits in a string.
	 *
	 * @since    1.0
	 * @param    string    $s     Input string.
	 * @param    int       $from  Starting position.
	 * @param    int       $to    Ending position
	 * @return   bool             True/False.
	 */
	protected function is_onlydigits( $s, $from, $to )
	{
		if ( $from > $to )
			return false;
		for ( $i = $from; $i < $to; $i++ ) {
			if ( $s[$i] < '0' || $s[$i] > '9' )
				return false;
		}
		return true;
	}
	/**
	 * Test if string is composed of digits and commas.
	 *
	 * @since    1.0
	 * @param    string    $s     Input string.
	 * @param    int       $from  Starting position.
	 * @param    int       $to    Ending position
	 * @return   bool             True/False.
	 */
	protected function is_onlydigitswithcommas( $s, $from, $to )
	{
		if ( $from > $to )
			return false;
		for ( $i = $from; $i < $to; $i++ ) {
			if ( $s[$i] != ',' && ( $s[$i] < '0' || $s[$i] > '9' ) )
				return false;
		}
		return true;
	}
}
/**
 * This is an unknown metric type and is totally unexpected.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerMetricTypeUnknown extends ObjtrackerMetricBase
{
	/**
	 * Constructor for ObjtrackerMetricTypeUnknown
	 *
	 * @since    1.0
	 * @param    char      $sType    Type of metric
	 * @param    string    $sValue   Value of metric
	 * @return   void
	 */
	function __construct( $sType, $sRaw )
	{
		$this->sRawValue  = $sRaw;
		$this->sRawLength = strlen( $sRaw );
		$this->sError     = "ObjtrackerMetricTypeUnknown '" . $sType . __( "' is unknown" );
	}
}
/**
 * Manage a percentage metric value.
 *
 * Percentage values look like 100% 0% 200% and have no decimal points.
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerMetricPercent extends ObjtrackerMetricBase
{
	/**
	 * Constructor for ObjtrackerMetricPercent
	 *
	 * @since    1.0
	 * @param    char      $sType    Type of metric
	 * @param    string    $sValue   Value of metric
	 * @return   void
	 */
	function __construct( $sRaw )
	{
		$this->sRawValue   = $sRaw;
		$this->sReformated = $sRaw;
		$this->sRawLength  = strlen( $sRaw );
		if ( $this->sRawLength == 0 )
			$this->sError = __( 'is required' );
		elseif ( $this->sRawLength < 2 )
			$this->sError = __( 'nnn%, is too short' );
		elseif ( substr( $sRaw, $this->sRawLength - 1, 1 ) != '%' )
			$this->sError = __( 'nnn%, is missing % sign' );
		elseif ( !$this->is_onlydigits( $sRaw, 0, $this->sRawLength - 1 ) )
			$this->sError = __( 'nnn of nnn% has invalid digits' );
		else {
			$this->sError      = '';
			$value             = substr( $this->sRawValue, 0, $this->sRawLenth - 1 );
			$this->fValue      = $value;
			$this->sReformated = $value . '%';
		}
	}
}
/**
 * Manage interger metric type.
 *
 * Integers look like 1 or 1,000 or 1,000,000.
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerMetricInteger extends ObjtrackerMetricBase
{
	/**
	 * Constructor for ObjtrackerMetricInteger
	 *
	 * @since    1.0
	 * @param    char      $sType    Type of metric
	 * @param    string    $sValue   Value of metric
	 * @return   void
	 */
	function __construct( $sRaw )
	{
		$this->sRawValue   = $sRaw;
		$this->sReformated = $sRaw;
		$this->sRawLength  = strlen( $sRaw );
		if ( $this->sRawLength == 0 )
			$this->sError = __( 'is required' );
		elseif ( !$this->is_onlydigitswithcommas( $sRaw, 0, $this->sRawLength ) )
			$this->sError = __( 'nn,nnn,nnn has invalid digits' );
		else {
			try {
				$value = str_replace( ',', '', $this->sRawValue );
				$value = $value + 0;
				$this->sReformated = sprintf( '%d', $value );
				$this->fValue = $value;
				$this->sError = '';
		}
			catch ( Exception $e ) {
				$this->sError = __( 'nn,nnn,nnn format error' );
			}
		}
	}
}
/**
 * Manage ratio metric type.
 *
 * Ratios are are comparison of two integers where the second is not zero.
 * such as 3:4.  4:8 is not currently reduced to 1:2.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerMetricRatio extends ObjtrackerMetricBase
{
	/**
	 * Constructor for ObjtrackerMetricRatio
	 *
	 * @since    1.0
	 * @param    char      $sType    Type of metric
	 * @param    string    $sValue   Value of metric
	 * @return   void
	 */
	function __construct( $sRaw )
	{
		$this->sRawValue   = $sRaw;
		$this->sReformated = $sRaw;
		$this->sRawLength  = strlen( $sRaw );

		$index = strpos( $sRaw , ':' );
		if ( $this->sRawLength == 0 )
			$this->sError = __( 'is required' );
		elseif ( strlen( $sRaw ) < 3 )
			$this->sError = __( 'n:n is too short' );
		elseif ( $index === false )
			$this->sError = __( 'n:n is missing extends sign' );
		else {
			if ( !$this->is_onlydigits( $sRaw, 0, $index ) )
				$this->sError = __( 'n:n has invalid digits' );
			elseif ( !$this->is_onlydigits( $sRaw, $index + 1, $this->sRawLength ) )
				$this->sError = __( 'n:n has invalid digits' );
			else {
				$this->sError = '';
				try {
					$int1 = substr( $this->sRawValue, 0, $index );

					$sRight = substr( $this->sRawValue, $index + 1, $this->sRawLength - $index - 1 );

					if ( $sRight == 0 )
						$this->sError = __( 'm of n:m can not be zero' );
					else {
						$this->fValue = $int1 / sRight;
						$this->sReformated = int1 . ':' . sRight;
					}
				}
				catch ( Exception $e ) {
					$this->sError = __( 'n:n format error' );
				}
			}
		}
	}
}
/**
 * Manage date metric type.
 *
 * Date format is only yyyy-mm-dd, yyyy-m-dd, yyyy-mm-d or yyyy-m-d
 * which are normalized to yyyy-mm-dd.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerMetricDate extends ObjtrackerMetricBase
{
	/**
	 * Constructor for ObjtrackerMetricDate
	 *
	 * @since    1.0
	 * @param    char      $sType    Type of metric
	 * @param    string    $sValue   Value of metric
	 * @return   void
	 */
	function __construct( $sRaw )
	{
		$this->sRawValue   = $sRaw;
		$this->sReformated = $sRaw;
		$this->sRawLength  = strlen( $sRaw );

		if ( $this->sRawLength == 0 )
			$this->sError = 'is required';
		elseif ( strlen( $sRaw ) < 8 )
			$this->sError = __( 'yyyy-mm-dd is too short' );
		elseif ( strlen( $sRaw ) > 10 )
			$this->sError = __( 'yyyy-mm-dd is too long' );
		else {
			$index1 = strpos( $sRaw, '-' );
			if ( $index1 === false || $index1 != 4)
				$this->sError = __( 'in yyyy-mm-dd format, year is not 4 digits' );
			else {
				$index2 = strpos( $sRaw, '-', $index1 + 1 );
				if ( $index2 === false )
					$this->sError = __( 'yyyy-mm-dd format error2' );
				elseif ( $index2 < 1 || $index2 - $index1 > 3 || strlen( $sRaw ) - $index2 - 1 < 1 || strlen( $sRaw ) - $index2 - 1 > 3 )
					$this->sError = __( 'yyyy-mm-dd format error2' );
				else {
					$sYYYY = substr( $sRaw, 0, 4 );
					$sMM   = substr( $sRaw, 5, $index2 - $index1 - 1 );
					$sDD   = substr( $sRaw, $index2 + 1, $this->sRawLength - $index2 - 1 );

					if ( !$this->is_onlydigits( $sMM, 0, strlen( $sMM ) ) )
						$this->sError = __( 'in yyyy-mm-dd format, mm is not numeric (' ) . $sMM . ')';
					elseif ( $sMM == 0 )
						$this->sError = __( 'in yyyy-mm-dd format, mm must not be zero' );
					elseif ( !$this->is_onlydigits( $sDD, 0, strlen( $sDD ) ) )
						$this->sError = __( 'in yyyy-mm-dd format, dd is not numeric (' ) . $sDD . ')';
					elseif ( $sDD == 0 )
						$this->sError = __( 'in yyyy-mm-dd format, dd must not be zero' );
					elseif ( !$this->is_onlydigits( $sYYYY, 0, strlen( $sYYYY ) ) )
						$this->sError = __( 'in yyyy-mm-dd format, yyyy is not numeric (' ) . $sYYYY . ')';
					else {
						// $this->sError = 'in yyyy-mm-dd format, yyyy (' . $sYYYY . ')mm (' . $sMM . 'dd (' . $sDD . ') )';
						try {
							$dt                = new DateTime( $sYYYY . '-' . $sMM . '-' . $sDD );
							$this->sReformated = date_format( $dt, 'Y-m-d' );
							$this->fValue      = 12 * 31 * $sYYYY + 31 * $sMM + $sDD;
						}
						catch ( Exception $e ) {
							$this->sError = __( 'yyyy-mm-dd has invalid date' );
						}
					}
				}
			}
		}
	}
}
/**
 * Manage dollar metric type.
 *
 * Dollars look like integers with a '$' pasted on the front.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerMetricDollar extends ObjtrackerMetricBase
{
	/**
	 * Constructor for ObjtrackerMetricDollar
	 *
	 * @since    1.0
	 * @param    char      $sType    Type of metric
	 * @param    string    $sValue   Value of metric
	 * @return   void
	 */
	function __construct( $sRaw )
	{
		$this->sRawValue   = $sRaw;
		$this->sReformated = $sRaw;
		$this->sRawLength  = strlen( $sRaw );

		if ( $this->sRawLength == 0 )
			$this->sError = __( 'is required' );
		elseif ( $this->sRawLength < 2 )
			$this->sError = __( '$nn,nnn,nnn is too short' );
		elseif ( substr( $sRaw, 0, 1 ) != '$' )
			$this->sError = __( '$nn,nnn,nnn is missing $ sign' );
		elseif ( !$this->is_onlydigitswithcommas( $sRaw, 1, $this->sRawLength ) )
			$this->sError = __( '$nn,nnn,nnn has invalid digits' );
		else {
			try {
				$this->sError      = '';
				$value             = str_replace( ',', '', substr( $this->sRawValue, 1, $this->sRawLength ) );
				$this->fValue      = $value;
				$value             = $value + 0;
				$this->sReformated = '$' . $value;
			}
			catch ( Exception $e ) {
				$this->sError = __( '$nn,nnn,nnn format error' );
			}
		}
	}
}
/**
 * Class is used to validate a measurement.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerValidateMeasurement
{
	/**
	 * Holds the metric object.
	 *
	 * @var ObjtrackerMetricValue
	 * @access private
	 * @since 1.0
	 */
	protected $mvTarget;

	/**
	 * Has '' or an error message.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $sError = '';

	/**
	 * Constructor for ValidateMeasurement
	 *
	 * @since    1.0
	 * @param    char      $sType    Type of metric
	 * @param    string    $sValue   Value of metric
	 * @return   void
	 */
	function __construct( $MetricTypeID, $Target )
	{
		$this->mvTarget = new ObjtrackerMetricValue( $MetricTypeID, $Target );
		$this->sError   = $this->mvTarget->error();
	}

	/**
	 * Retrieve message if metric is incorrectly formed.
	 *
	 * @since    1.0
	 * @return   string                Message if metric is incorrectly formed.
	 */
	function error()
	{
		return $this->sError;
	}


	/**
	 * Retrieve normalized value of original value.
	 *
	 * By normalized, 000 is changed to 0, $4444,10 changed to $444,410, etc.
	 *
	 * @since    1.0
	 * @return   string                The normalized value.
	 */
	function normalized_target()
	{
		return $this->mvTarget->normalized();
	}
}
/**
 * Class is used to validate the three target values together as a group.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerValidateTargets
{
	/**
	 * Holds the metric object.
	 *
	 * @var ObjtrackerMetricValue
	 * @access private
	 * @since 1.0
	 */
	protected $mvTarget;

	/**
	 * Holds the metric object.
	 *
	 * @var ObjtrackerMetricValue
	 * @access private
	 * @since 1.0
	 */
	protected $mvTarget1;

	/**
	 * Holds the metric object.
	 *
	 * @var ObjtrackerMetricValue
	 * @access private
	 * @since 1.0
	 */
	protected $mvTarget2;

	/**
	 * Has '' or an error message.
	 *
	 * @var string
	 * @access private
	 * @since 1.0
	 */
	protected $sError = '';

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
	function __construct( $MetricTypeID, $Target, $Target1, $Target2 )
	{
		$this->mvTarget  = new ObjtrackerMetricValue( $MetricTypeID, $Target );
		$this->mvTarget1 = new ObjtrackerMetricValue( $MetricTypeID, $Target1 );
		$this->mvTarget2 = new ObjtrackerMetricValue( $MetricTypeID, $Target2 );

		if ( strlen( $this->mvTarget->error() ) != 0 ) {
			$this->sError = 'Target:' . ' ' . $this->mvTarget->error();
		} elseif ( strlen( $this->mvTarget1->error() ) != 0 ) {
			$this->sError = 'Near target: ' . $this->mvTarget1->error();
		} elseif ( strlen( $this->mvTarget2->error() ) != 0 ) {
			$this->sError = 'Far target: ' . $this->mvTarget2->error();
		} elseif ( $this->mvTarget->value() > $this->mvTarget1->value() && $this->mvTarget1->value() > $this->mvTarget2->value() ) {
			$this->sError = '';
		} elseif ( $this->mvTarget->value() < $this->mvTarget1->value() && $this->mvTarget1->value() < $this->mvTarget2->value() ) {
			$this->sError = '';
		} else {
			$this->sError = __( 'Target must be either (1) greater than Near and Near greater that Far or (2) less than Near and Near less than Far.' );
		}
	}
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
	function error()
	{
		return $this->sError;
	}


	/**
	 * Retrieve normalized value of original value.
	 *
	 * By normalized, 000 is changed to 0, $4444,10 changed to $444,410, etc.
	 *
	 * @since    1.0
	 * @return   string                The normalized value.
	 */
	function normalized_target()
	{
		return $this->mvTarget->normalized();
	}


	/**
	 * Retrieve normalized value of original value.
	 *
	 * By normalized, 000 is changed to 0, $4444,10 changed to $444,410, etc.
	 *
	 * @since    1.0
	 * @return   string                The normalized value.
	 */
	function normalized_target1()
	{
		return $this->mvTarget1->normalized();
	}


	/**
	 * Retrieve normalized value of original value.
	 *
	 * By normalized, 000 is changed to 0, $4444,10 changed to $444,410, etc.
	 *
	 * @since    1.0
	 * @return   string                The normalized value.
	 */
	function normalized_target2()
	{
		return $this->mvTarget2->normalized();
	}
}

?>
