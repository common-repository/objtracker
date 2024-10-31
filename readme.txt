=== Objective Tracker === 
Contributors: DanRoller
Donate link: http://techaccess.org/
Tags: Balanced Scorecard, Management, Objectives, Goals, Targets, Measurements
Requires at least: 3.5 
Tested up to: 3.6
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Description: Define your organization's objectives and collect data on how well you have succeeded in meeting those objectives.

== Description ==

Objective Tracker - A Balanced Scorecard.

A Balanced Scorecard is a strategic planning and management system that is used extensively in business and industry, government, and nonprofit organizations worldwide to align business activities to the vision and strategy of the organization, improve internal and external communications, and monitor organization performance against strategic goals. 

== Requirments ==

1. MySQL, development occured on versions 5.5 and 5.6.
2. Facility such as a WordPress plugin to add either `objtracker` or `objtrackeradmin` WordPress user capability.  This developer used the Capability Manager Enhanced plugin.

== Installation ==

1. Select objtracker from WordPress's installable plugs or download then unzip as `objtracker` in your `/wp-content/plugins/` directory.
2. Activate the `objtracker` plugin through the `Plugins` menu in WordPress.
3. See `/wp-content/plugins/objtracker/help` directory for install, admin, and user pdf documents including a description of using Capabilties Manager Extended.
4. Add WordPress user capability `objtrackeradmin` to the user ids that you want to administer the balanced scorecard using Capabilties Manager Extended or your own method.
5. First user who enters will drive the generation of the database components - a prompted dialog.
6. Additional users need (a) capability `objtracker` to have tool menu (b) user id added by the objtracker admin using menu->People page.

== Frequently Asked Questions ==

* What is the state of testing on various platforms?
Code has been tested by developer on a Windows 8 machine and a Fedora image under VirtualBox.

* Isn't this exclusively based on USA currency, integer and date formats?
Yes, for version 1.0 this is true, but it is relatively easy to add additional and/or alternate metric types.

* How do I contribute to Objective Tracker?
If you find this tool useful, consider a contribution to the Technology Access Foundation (TAF). 
Click the help link in the Objective Tracker for more information.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.png.  
   The image shows the Objectives Page that lists the defined objectives.

== Changelog ==

= 1.0.7 =
Icon changes.

= 1.0.6 =
1. Installation problem for Linux based affected by Windows/Linux difference in MySQL case of saved table names.
2. Alerts, Objectives, and Fiscal Years affected by Windows/Linux difference in MySQL case stored procedures.
3. Links to PDFs failed on Help page for Chrome and Firefox browsers.
 
= 1.0.4 =
1. Prior update added '%%' in several procs and one table.  This update corrects those items.
2. Excel download missing data and was cached. 'Excel Download' changed to 'Spreadsheet Download'.
3. Usage page instructions corrected.

= 1.0.3 =
1. Broke db setup into a dialog to identify errors and run under limited timeout.
2. Database function objtrackerF_StatusCompare was in error. 
3. Database components in the sql directory have been remained.
4. All function, stored procedures, and tables now have 'objtracker' prefix so
   items match what is installed in the database.
   This facilates deleting all database components by changing directory to the
   objtracker plugins's sql/drops directory and executing mysql on each member, example
     MySql -h localhost -uroot -D wordpressnnn < filename

= 1.0.2 =
1. Improved install documentation related to users of "Capability Manager" plugin.
2. Fix fatal installation error on non-Windows OS, Warning: file_get_contents().

= 1.0.1 =
1. Changed installation wpdb prepare warning during first entry,
2. Broken links for css, js, and images if WordPress installed in sub-directory. 

= 1.0.0 =
Initial release

== Upgrade Notice ==

= 1.0.2 =
Required if site not running Windows.

= 1.0.1 =
Required if WordPress installed in sub-directory. 


