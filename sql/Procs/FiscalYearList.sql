/*
	ID of [objtrackerT_FiscalYear] and [objtrackerT_FyCalendar] represents the ID of the first year of the fiscal year.
*/
DROP PROCEDURE IF EXISTS objtrackerP_FiscalYearList;
DELIMITER $$
/* DATE_FORMAT(NOW(),'%d %b %y')
	CALL objtrackerP_FiscalYearList(2,1)
*/
CREATE PROCEDURE objtrackerP_FiscalYearList(
	C_CallerOrg		INT
	,C_CallerUser	INT
)
BEGIN
	SELECT FirstMonth INTO @FirstMonth FROM objtrackerT_Organization WHERE ID = C_CallerOrg;

	SELECT
		ID AS C_ID
		,objtrackerF_FormatFiscalYear(ID,@FirstMonth) AS C_FormatedFiscalYear
		,Title AS C_Title
		,Active AS C_Active
		,(SELECT COUNT(*) FROM objtrackerT_Objective WHERE FiscalYear1 <= objtrackerT_FiscalYear.ID AND FiscalYear2 >= objtrackerT_FiscalYear.ID) AS C_Usage
		,objtrackerF_FormatDate(Track_Changed) AS C_Track_Changed
		,objtrackerF_FormatSortedDate(Track_Changed) AS C_Track_SortedChanged
		,Track_Userid AS C_Track_Userid
	FROM objtrackerT_FiscalYear
	WHERE OrganizationID = C_CallerOrg
	ORDER BY ID;
END
$$
