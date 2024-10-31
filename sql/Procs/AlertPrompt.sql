DROP PROCEDURE IF EXISTS objtrackerP_AlertPrompt;
DELIMITER $$
/*
 call objtrackerP_AlertPrompt (1 ,'2011-10-01')
 call objtrackerP_AlertPrompt (1 ,'2012-10-01')
*/
CREATE PROCEDURE objtrackerP_AlertPrompt (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	,C_ID					INT 
	,C_PeriodStarting		VARCHAR(32)
)
BEGIN
DECLARE C_FiscalYear INT;
SELECT objtrackerF_FiscalYear(C_CallerOrg) INTO C_FiscalYear;
SELECT 
		objtrackerT_Objective.ID AS C_ID
		,objtrackerT_Objective.FiscalYear1 AS C_FiscalYear1
		,objtrackerT_Objective.FiscalYear2 AS C_FiscalYear2
		,objtrackerT_Objective.FrequencyID AS C_FrequencyID
		,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
		,objtrackerT_Objective.Title AS C_Title
		,objtrackerT_ObjectiveType.Title AS C_Type
		,objtrackerT_Objective.Description AS C_Description
		,objtrackerT_Objective.Source AS C_Source
		,objtrackerT_Frequency.Title AS C_Frequency
		,objtrackerT_MetricType.Title AS C_MetricType
		,objtrackerT_MetricType.Description AS C_MetricTypeDesc 
		,FY1.Title AS C_Fy1Title
	  ,FY2.Title AS C_Fy2Title
		,objtrackerT_Target.Target AS C_Target
		,objtrackerT_Target.Target1 AS C_Target1 
		,objtrackerT_Target.Target2 AS C_Target2 
	,objtrackerF_FormatDate(objtrackerT_Objective.Track_Changed) AS C_Track_Changed
	,objtrackerF_FormatSortedDate(objtrackerT_Objective.Track_Changed) AS C_Track_SortedChanged
	,objtrackerT_Objective.Track_Userid AS C_Track_Userid
	,CASE WHEN objtrackerT_Measurement.Measurement is null then '' else objtrackerT_Measurement.Measurement END AS C_Measurement
	,CASE WHEN objtrackerT_Measurement.Notes is null then '' else objtrackerT_Measurement.Notes END AS C_Notes
FROM
	objtrackerT_Objective 
	JOIN objtrackerT_ObjectiveType		ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID
							   AND objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Person				ON objtrackerT_Person.ID					= objtrackerT_Objective.OwnerID
							   AND objtrackerT_Person.OrganizationID		= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Department			ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
							   AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID
	JOIN objtrackerT_Frequency			ON objtrackerT_Frequency.ID				= objtrackerT_Objective.FrequencyID
							   AND objtrackerT_Frequency.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_MetricType			ON objtrackerT_MetricType.ID				= objtrackerT_Objective.MetricTypeID
							   AND objtrackerT_MetricType.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FiscalYear AS	FY1	ON FY1.ID					= objtrackerT_Objective.FiscalYear1
							   AND FY1.OrganizationID		= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FiscalYear AS	FY2	ON FY2.ID					= objtrackerT_Objective.FiscalYear2
							   AND FY2.OrganizationID		= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Target				ON objtrackerT_Target.ID				= objtrackerT_Objective.ID 
							   AND objtrackerT_Target.OrganizationID	= objtrackerT_Objective.OrganizationID
	LEFT OUTER JOIN objtrackerT_Measurement ON objtrackerT_Objective.ID					= objtrackerT_Measurement.ObjectiveID
							    AND objtrackerT_Objective.OrganizationID		= objtrackerT_Measurement.OrganizationID
  								AND objtrackerT_Measurement.OrganizationID 	= C_CallerOrg 
								AND objtrackerT_Measurement.PeriodStarting	= C_PeriodStarting
WHERE objtrackerT_Objective.OrganizationID 	= C_CallerOrg AND objtrackerT_Objective.ID 				= C_ID
  AND objtrackerT_Target.OrganizationID 		= C_CallerOrg AND objtrackerT_Target.FiscalYear  			= C_FiscalYear	
;
END
$$
