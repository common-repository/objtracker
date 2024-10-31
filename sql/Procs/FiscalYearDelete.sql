/*
	ID of [objtrackerT_FiscalYear] and [objtrackerT_FyCalendar] represents the ID of the first year of the fiscal year.
*/
DROP PROCEDURE IF EXISTS objtrackerP_FiscalYearDelete;
DELIMITER $$
/*
	EXEC objtrackerP_FiscalYearDelete 1, 'ab','cd',1
*/
CREATE PROCEDURE objtrackerP_FiscalYearDelete(
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_ID			INT   
)
BEGIN
	DELETE FROM objtrackerT_FiscalYear  WHERE OrganizationID = C_CallerOrg AND ID = C_ID;
	DELETE FROM objtrackerT_FyCalendar  WHERE OrganizationID = C_CallerOrg AND FiscalYear = C_ID;
	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
 
END
$$
