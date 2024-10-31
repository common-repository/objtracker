DROP PROCEDURE IF EXISTS objtrackerP_TargetObjList;
DELIMITER $$
CREATE PROCEDURE objtrackerP_TargetObjList (
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_ID 			INT
)
BEGIN
	SELECT 
		objtrackerT_Target.ID AS C_ID
		,objtrackerT_Target.FiscalYear AS C_FiscalYear
		,objtrackerT_FiscalYear.Title AS C_FiscalYearTitle
		,objtrackerT_Target.Target AS C_Target
		,objtrackerT_Target.Target1 AS C_Target1
		,objtrackerT_Target.Target2 AS C_Target2
		,objtrackerF_FormatSortedDate(objtrackerT_Target.Track_Changed) AS C_Track_SortedChanged
		,objtrackerF_FormatDate(objtrackerT_Target.Track_Changed) AS C_Track_Changed
		,objtrackerT_Target.Track_Userid AS C_Track_Userid
	FROM objtrackerT_Target 
	JOIN objtrackerT_FiscalYear ON objtrackerT_Target.FiscalYear = objtrackerT_FiscalYear.ID
					AND  objtrackerT_Target.OrganizationID = objtrackerT_FiscalYear.OrganizationID
	WHERE objtrackerT_Target.ID = C_ID
	  AND objtrackerT_Target.OrganizationID = C_CallerOrg;
END
$$
