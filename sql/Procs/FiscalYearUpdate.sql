/*
	ID of [objtrackerT_FiscalYear] and [objtrackerT_FyCalendar] represents the ID of the first year of the fiscal year.
*/
DROP PROCEDURE IF EXISTS objtrackerP_FiscalYearUpdate;
DELIMITER $$
/*
	CALL objtrackerP_FiscalYearUpdate(2013,'2013x2014','me')
	select * from objtrackerT_FiscalYear
*/
CREATE PROCEDURE objtrackerP_FiscalYearUpdate(
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_ID				INT
	,C_Title			VARCHAR (32)
)
BEGIN
	IF EXISTS (SELECT * FROM objtrackerT_FiscalYear WHERE OrganizationID = C_CallerOrg AND Title = C_Title AND ID != C_ID) THEN
		SELECT 'FyUpdDup' AS C_ErrorID, 'Title already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

		UPDATE objtrackerT_FiscalYear SET
			Title = C_Title
			,Track_Changed = Now()
			,Track_Userid = @UserName
		WHERE OrganizationID = C_CallerOrg AND ID = C_ID;

		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
