CALL objtrackerP_Audit_Define_table (
	10000,'objtrackerT_Audit', 'Audit',	'This table contains the before and after values from user changes to audited tables.'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10001,'e','Track_Guid', 'ID',	'The unique row identifier of the audit table'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10002,'e','Track_Date', 'Date/Time',	'Date/time of change.'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10003,'e','Track_Userid', 'User',	'User who made the change.'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10004,'e','Track_TableID', 'Table ID',	'ID of the table changed. (objtrackerT_AuditTable)'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10005,'e','Track_ID', 'ID2',	'ID for additional uniqueness.'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10006,'e','Track_ColumnID', 'Column ID',	'ID of column changed. (objtrackerT_AuditColumn)'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10007,'e','Track_Action',  'Add/Change/Delete',	'A-Add, U-Update, D-Delete'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10008,'e','Track_SubAction', 'Add/Change/Delete',	'A-Add, U-Update, D-Delete'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10009,'e','Track_Before', 'Prior Value',	'Value before change'
	);
CALL objtrackerP_Audit_Define_Column(
	10000,10010,'e','Track_After', 'New Value',	'Value after change'
	);
CALL objtrackerP_Audit_Define_table (
	11000,'objtrackerT_AuditIndex', 'Audit index',	'This table contains index  of changes to audited tables.'
	);
CALL objtrackerP_Audit_Define_Column(
	11000,11001,'e','Track_Guid', 'ID',	'The unique row identifier of the audit table'
	);
CALL objtrackerP_Audit_Define_Column(
	11000,11002,'e','Track_Date', 'Date/Time',	'Date/time of change.'
	);
CALL objtrackerP_Audit_Define_Column(
	11000,11003,'e','Track_Userid', '???',	'???'
	);
CALL objtrackerP_Audit_Define_Column(
	11000,11004,'e','Track_TableID', 'Table ID',	'ID of the table changed.'
	);
CALL objtrackerP_Audit_Define_Column(
	11000,11005,'e','Track_ID', '???',	'???'
	);
CALL objtrackerP_Audit_Define_Column(
	11000,11006,'e','Track_PID', '???',	'???'
	);
CALL objtrackerP_Audit_Define_Column(
	11000,11007,'e','Track_Action', 'Add/Change/Delete',	'A-Add, U-Update, D-Delete'
	);
CALL objtrackerP_Audit_Define_Column(
	11000,11008,'e','Track_Name', 'Changed by',	'User name who made change'
	);
CALL objtrackerP_Audit_Define_table (
	30000,'objtrackerT_AuditTable', 'Audited tables',	'This table documents each scorecard table.'
	);
CALL objtrackerP_Audit_Define_Column(
	30000,30001,'e','ID', 'ID',	'The unique row identifier of the audited table.'
	);
CALL objtrackerP_Audit_Define_Column(
	30000,30002,'e','Name', 'Table Name',	'Name of the table in the database.'
	);
CALL objtrackerP_Audit_Define_Column(
	30000,30003,'e','Description', 'Description',	'Casual term name of the table.'
	);
CALL objtrackerP_Audit_Define_Column(
	30000,30004,'e','Documentation', 'Documentation',	'Full description of the table'
	);
CALL objtrackerP_Audit_Define_table (
	31000,'objtrackerT_AuditColumn', 'Audited columns',	'This table contains name and description of each column of a scorecard table.'
	);
CALL objtrackerP_Audit_Define_Column(
	31000,31001,'e','ID', 'ID',	'The unique row identifier of the audited table.'
	);
CALL objtrackerP_Audit_Define_Column(
	31000,31002,'e','TableID', 'Table ID',	'Auditing ID of table that the column belongs.'
	);
CALL objtrackerP_Audit_Define_Column(
	31000,31003,'e','Type', 'Data Type',	'Database''s type of data for this column.'
	);
CALL objtrackerP_Audit_Define_Column(
	31000,31004,'e','Name', 'Column Name',	'Name of the column in the database.'
	);
CALL objtrackerP_Audit_Define_Column(
	31000,31005,'e','Description', 'Description',	'Casual term name of the column.'
	);
CALL objtrackerP_Audit_Define_Column(
	31000,31006,'e','Documentation', 'Documentation',	'Full description of the column'
	);
CALL objtrackerP_Audit_Define_table (
	40000,'objtrackerT_FyCalendar', 'Fiscal years extension',	'This table defines each frequency''s starting period for a defined fiscal year.'
	);
CALL objtrackerP_Audit_Define_Column(
	40000,40001,'e','PeriodStarting', 'Period Starting',	'Date that starts the measurement period for this frequency type'
	);
CALL objtrackerP_Audit_Define_Column(
	40000,40002,'e','FrequencyID', 'Frequency ID',	'The frequency type'
	);
CALL objtrackerP_Audit_Define_Column(
	40000,40003,'e','FiscalYear', 'Fiscal Year',	'First year of the Fiscal Year'
	);
