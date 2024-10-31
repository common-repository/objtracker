DROP PROCEDURE IF EXISTS objtrackerP_ExtendList;
DELIMITER $$
 
/*
	select FiscalYear2 FROM objtrackerT_Objective  
	call objtrackerP_ExtendList( 2013 )
	call objtrackerP_ExtendList( 2014 )
*/
CREATE PROCEDURE objtrackerP_ExtendList ( 
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_FiscalYear2 	INT
)
BEGIN
	SELECT 
		objtrackerT_Objective.ID AS C_ID
		,objtrackerT_Objective.Title AS C_Title
		,objtrackerT_Department.Title2 AS C_DeptTitle2
		,FY1.Title AS C_FY1Title
		,FY2.Title AS C_FY2Title
	FROM objtrackerT_Objective 
	JOIN objtrackerT_Person			ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID
						   AND objtrackerT_Person.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Department		ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
						   AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID
	JOIN objtrackerT_FiscalYear AS	FY1	ON FY1.ID			= objtrackerT_Objective.FiscalYear1
						   AND FY1.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FiscalYear AS	FY2	ON FY2.ID			= objtrackerT_Objective.FiscalYear2
						   AND FY2.OrganizationID	= objtrackerT_Objective.OrganizationID
	WHERE objtrackerT_Objective.FiscalYear2 = C_FiscalYear2
	  AND objtrackerT_Objective.OrganizationID = C_CallerOrg 
	ORDER BY objtrackerT_Objective.Title,objtrackerT_Objective.ID
	;
END
$$
