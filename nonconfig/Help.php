<?php
/**
 * Manage help diaglog.
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
function bs_help( $bsdriver, $bsuser )
{

	$page = new BsHelpPage( $bsdriver, $bsuser, 'Help' );
	return $page->response();
}

/**
 * Help processing within a class.
 *
 * @package objtracker
 * @category Class
 * @author  Dan Roller <bsdanroller@gmail.com>
 */
class BsHelpPage extends ObjtrackerPage
{
	/**
	 * Returns this page unique html text.
	 *
	 * @since    1.0
	 * @return   string              Page's unique html text.
	 */
	public function response()
	{
		$bsdriver = $this->bsdriver;
		$bsuser   = $this->bsuser;

		return
			$bsdriver->description_headertext( __( 'Documents (pdf)' ) )
			. ObjtrackerEasyStatic::table_start( "cellspacing='0' style='border-collapse:collapse;position:relative;left:20px;'" )
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td(
					'',
					"<a href='" . $bsdriver->PathDownload
					. "sc_menu=ShowDocument&doc=Admin.pdf&mimetype=application/pdf&fname=Admin.pdf'>"
					. __( 'Adminstrators Guide' ) . '</a>&nbsp;&nbsp;&nbsp;'
					)
			. ObjtrackerEasyStatic::table_td(
					'',
					"For those who manage the Balanced Scorecard's users, departments, fiscal years, and objectives."
					)
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td(
					'',
					"<a href='" . $bsdriver->PathDownload
					. "sc_menu=ShowDocument&doc=Install.pdf&mimetype=application/pdf&fname=Install.pdf'>"
					. 'Installers Guide' . "</a>\n"
					)
			. ObjtrackerEasyStatic::table_td(
					'',
					'For those who install Balanced Scorecard'
					)
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_tr_start( '' )
			. ObjtrackerEasyStatic::table_td(
					'',
					"<a href='" . $bsdriver->PathDownload
					. "sc_menu=ShowDocument&doc=User.pdf&mimetype=application/pdf&fname=User.pdf'>"
					. 'Users Guide' . "</a>\n"
					)
			. ObjtrackerEasyStatic::table_td(
					'',
					'For those who enter measurements for objectives.'
					)
			. ObjtrackerEasyStatic::table_tr_end()
			. ObjtrackerEasyStatic::table_end()
			. $this->description();
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

		$bsdriver->ShowInfo = ObjtrackerUser::UIShowYes;

		$txt  = '<br /><br />';
		$txt .= $bsdriver->description_headertext( __( 'Purpose' ) );
		$txt .= $bsdriver->description_text( __( 'This is a simplified Balance Scorecard tool for a broader audience than similar tools.&nbsp;&nbsp;Balanced Scorecard is a strategic planning and management system that is used extensively in business and industry, government, and nonprofit organizations worldwide to align business activities to the vision and strategy of the organization, improve internal and external communications, and monitor organization performance against strategic goals.' ) );
		$txt .= '<br />';

		$txt .= $bsdriver->description_headertext( __( 'History' ) );

		$txt .= $bsdriver->description_text( __( "In 2012, I was approached by a local non-profit organization, Technology Access Foundation (TAF) to create a customized Balanced Scorecard Tool to help align business activities to the foundation's strategic plan.&nbsp;&nbsp;Based on data from a previous tracking system, I created a web/database system using Microsoft SQL Server, Internet Information Services for Windows, and Visual Studio Express.&nbsp;&nbsp;Using these tools, I created a system that could easily be integrated into the foundation's internal infrastructure." ) );

		$txt .= $bsdriver->description_text( __( "In July of 2013, I released a 'generic' version of this tool, that uses WordPress as the platform with MySQL as the back end to the general public.&nbsp;&nbsp;This software is released under the GPLv2 (or later) - GNU General Public License." ) );
		$txt .= $bsdriver->description_text( __( 'If you find this tool useful, consider a contribution to the Technology Access Foundation (TAF).' ) );

		$txt .= $bsdriver->description_text( __( 'From the TAF website:<br />' ) );
		$txt .= $bsdriver->description_text( __( '<b>TAF is a nonprofit leader in science, technology, engineering and math (STEM) education. We address three problems that keep students of color away from STEM: low expectations, a shortage of role models in STEM and lack of access to a quality, STEM- focused education. We use STEM education as a tool for social change. Our targeted approach for addressing longstanding historical inequities for students of color develops leadership and citizenship in ALL students.</br>' ) );
		$txt .= $this->description_end();

		return $txt;
	}
}
?>
