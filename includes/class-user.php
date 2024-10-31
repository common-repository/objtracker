<?php
/**
 * Class contains the current user's info.
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */

/**
 * Class contains the current user's info.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerUser
{
	/**
	 * Holds '' or an error message if user unknown or database error.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Error;

	/**
	 * Holds the organization ID.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $OrgID;

	/**
	 * Holds the user ID.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $ID;

	/**
	 * True/False is the user a super admin?
	 *
	 * @var boolean
	 * @access public
	 * @since 1.0
	 */
	public $Root;

	/**
	 * Yes/No is the user a admin?
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Admin;

	/**
	 * Yes/No is the user a view only user?
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Viewer;

	/**
	 * Yes/No if viewer
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $UserName;

	/**
	 * Holds user's full name.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $FullName;

	/**
	 * An array of characters that define a user setting, typically used for show/hide panels.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $UiSettings;
		// position in UiSettings
		const UIShowInfo         = 0;
		const UIShowDetail       = 1;
		const UIShowTargets      = 2;
		const UIShowMeasurements = 3;

		// value in UiSettings
		const UIShowYes = 'S';
		const UIShowNo  = 'N';

	/**
	 * Holds department name.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Department;
	/**
	 * Holds organization name.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Organization;
	
	/**
	 * Holds the current fiscal years id (the first year of the fiscal year).
	 *
	 * @var int
	 * @access public
	 * @since 1.0
	 */
	public $FiscalYear1;

	/**
	 * Holds the title of the fiscal year.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $FiscalYearTitle;

	/**
	 * Holds number of the first month of the fiscal year, where January is 1.
	 *
	 * @var int
	 * @access public
	 * @since 1.0
	 */
	public $FirstMonth;

	/**
	 * Holds the organization's file upload path.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $UploadFsPath;

	/**
	 * Holds page trailer string.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 */
	public $Trailer;

	/**
	 * Constructor
	 *
	 * @since    1.0
	 * @param    int    $OrgID    Organization ID (aka blogid)
	 * @param    int    $ID       Person's ID (ID of T_People table)
	 * @param    bool   $Root     True is super admin
	 * @param    int    $Admin    True is an admin in T_People table)
	 * @param    int    $Viewer   True is an viewer (?)
	 * @param    string $UserName   User logon name
	 * @param    string $FullName   User's full name
	 * @param    string $UiSettings   String for saving show/hide state
	 * @param    string $Department   Department name
	 * @param    string $Organization   Organization name
	 * @param    string $FiscalYear1   Current fiscal year (first year of)
	 * @param    string $FiscalYearTitle   Title of the fiscal year
	 * @param    int    $FirstMonth   First month of the fiscal year
	 * @param    string $UploadFsPath   Path on server to upload files
	 * @param    string $Trailer       Page trailer
	 * @return   void
	 */
	function __construct(
		$OrgID,
		$ID,
		$Root,
		$Admin,
		$Viewer,
		$UserName,
		$FullName,
		$UiSettings,
		$Department,
		$Organization,
		$FiscalYear1,
		$FiscalYearTitle,
		$FirstMonth,
		$UploadFsPath,
		$Trailer
	)
	{
		$this->Error           = '';
		$this->OrgID           = $OrgID;
		$this->ID              = $ID;
		$this->Root            = $Root;
		$this->Admin           = $Admin;
		$this->Viewer          = $Viewer;
		$this->UserName        = $UserName;
		$this->FullName        = $FullName;
		$this->UiSettings      = $UiSettings;
		$this->Department      = $Department;
		$this->Organization    = $Organization;
		$this->FiscalYear1     = $FiscalYear1;
		$this->FiscalYearTitle = $FiscalYearTitle;
		$this->FirstMonth      = $FirstMonth;
		$this->UploadFsPath    = $UploadFsPath;
		$this->Trailer         = $Trailer;
	}

	/**
	 * Is this user a superadmin (for multi-organizations)?
	 *
	 * @since    1.0
	 * @return   bool                      true/false.
	 */
	public function is_root()
	{
		if ( $this->Root == 'Yes' )
			return true;
		else
			return false;
	}

	/**
	 * Is this user an admin?
	 *
	 * @since    1.0
	 * @return   bool                      true/false.
	 */
	public function is_admin()
	{
		if ( $this->Admin == 'Yes' )
			return true;
		else
			return false;
	}

	/**
	 * Should this type of show/hide description be shown?
	 *
	 * @since    1.0
	 * @param    const      Number of show/hide area.                           
	 * @return   bool       true/false.
	 */
	public function should_show( $item )
	{
		return substr( $this->UiSettings, $item, 1 ) == self::UIShowYes;
	}
	/**
	 * Should user's UISetting be changed due to user selection?
	 *
	 * @since    1.0
	 * @param    string  $item      UISetting string.                           
	 * @param    string  $value     Const value indicating which byte of string.                           
	 * @return   bool               true/false.
	 */
	public function should_update( $item, $value )
	{
		if ( substr( $this->UiSettings, $item, 1 ) == $value )
			return false;
		if ( $item == self::UIShowInfo )
			$this->UiSettings = $value . substr( $this->UiSettings, 1 );
		else
			$this->UiSettings = substr( $this->UiSettings, 0, $item )
				. substr( $value, 0, 1 )
				. substr( $this->UiSettings, $item + 1 );
		return true;
	}
}

/**
 * Class contains the current user's info.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class ObjtrackerBadUser extends ObjtrackerUser
{
	/**
	 * Constructor
	 *
	 * @since    1.0
	 * @param    string    $Error    Error message
	 * @return   void
	 */
	function __construct( $Error )
	{
		$this->Error = $Error;
		$this->OrgID = 1;
		$this->ID    = 1;
	}
}
?>
