DROP PROCEDURE IF EXISTS objtrackerP_FiscalYear2List;
DELIMITER $$
 
/*
	call objtrackerP_FiscalYear2List
*/
CREATE PROCEDURE objtrackerP_FiscalYear2List (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
)
BEGIN
	SELECT 
		objtrackerT_Objective.FiscalYear2 AS C_ID
		,objtrackerT_FiscalYear.Title AS C_Title
		,COUNT(*) AS C_Usage 
	FROM objtrackerT_Objective
	JOIN objtrackerT_FiscalYear ON objtrackerT_Objective.FiscalYear2 = objtrackerT_FiscalYear.ID
	WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg 
	GROUP BY FiscalYear2
	ORDER BY FiscalYear2 DESC
	;
END
$$
