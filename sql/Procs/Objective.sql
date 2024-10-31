DROP PROCEDURE IF EXISTS objtrackerP_Objective;
DELIMITER $$
/*
	call objtrackerP_Objective (1 ,1 ,1,'x')
	call objtrackerP_Objective (22 ,'x')
	Revised: 2012/12/03 Change full name to Inactive-[name] for inactive
*/
CREATE PROCEDURE objtrackerP_Objective (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	,C_ID		INT 
	,C_Label   VARCHAR(14)  
)
BEGIN
SELECT 
	objtrackerT_Objective.ID AS C_ID
	,C_Label AS C_Label
	,objtrackerT_Objective.FiscalYear1 AS C_FiscalYear1
	,objtrackerT_Objective.FiscalYear2 AS C_FiscalYear2
	,objtrackerT_Objective.FiscalYear1 AS C_WasFiscalYear1
	,objtrackerT_Objective.FiscalYear2 AS C_WasFiscalYear2
	,objtrackerT_Objective.Title AS C_Title
	,objtrackerT_Objective.Description AS C_Description
	,CASE WHEN objtrackerT_Objective.IsPublic = 1 THEN 'Public' ELSE 'Private' END AS C_IsPublic 
	,objtrackerT_Objective.TypeID AS C_TypeID
	,objtrackerT_Person.DepartmentID AS C_DepartmentID
	,objtrackerT_Objective.OwnerID AS C_OwnerID
	,objtrackerT_Objective.Source AS C_Source
	,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
	,objtrackerT_Objective.FrequencyID as C_FrequencyID
	,FY1.Title AS C_FY1Title
	,FY2.Title AS C_FY2Title
	,objtrackerT_ObjectiveType.Title AS C_Type
	,objtrackerT_Department.Title AS C_Department
	,CASE WHEN objtrackerT_Person.Active = 1 THEN objtrackerT_Person.FullName ELSE CONCAT('Inactive-',objtrackerT_Person.FullName) END AS C_Owner 
	,objtrackerT_Frequency.Title AS C_Frequency
	,objtrackerT_MetricType.Title AS C_MetricType
	,objtrackerT_MetricType.Description AS C_MetricTypeDesc
	,objtrackerF_FormatDate(objtrackerT_Objective.Track_Changed) AS C_Track_Changed
	,objtrackerF_FormatSortedDate(objtrackerT_Objective.Track_Changed) AS C_Track_SortedChanged
	,objtrackerT_Objective.Track_Userid AS C_Track_Userid
FROM
	objtrackerT_Objective 
	JOIN objtrackerT_ObjectiveType	ON objtrackerT_ObjectiveType.ID	= objtrackerT_Objective.TypeID
						AND	   objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Person			ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID
						AND	   objtrackerT_Person.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Department		ON objtrackerT_Department.ID			= objtrackerT_Person.DepartmentID
						AND	   objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID
	JOIN objtrackerT_Frequency		ON objtrackerT_Frequency.ID		= objtrackerT_Objective.FrequencyID
						AND	   objtrackerT_Frequency.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_MetricType		ON objtrackerT_MetricType.ID		= objtrackerT_Objective.MetricTypeID
						AND	   objtrackerT_MetricType.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FiscalYear AS	FY1	ON FY1.ID		= objtrackerT_Objective.FiscalYear1
						AND	   FY1.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FiscalYear AS	FY2	ON FY2.ID		= objtrackerT_Objective.FiscalYear2
						AND	   FY2.OrganizationID	= objtrackerT_Objective.OrganizationID
WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg
  AND objtrackerT_Objective.ID = C_ID;
END
$$
