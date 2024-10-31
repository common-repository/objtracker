DROP PROCEDURE IF EXISTS objtrackerP_Dashboard;
DELIMITER $$
 
CREATE PROCEDURE objtrackerP_Dashboard (
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_FiscalYear 	INT
 )
BEGIN
	
	-- Convert input fy to date time range
	DECLARE C_FirstMonth INT;
	DECLARE C_FY1, C_FY2 DateTime;
	SELECT FirstMonth INTO C_FirstMonth FROM objtrackerT_Organization WHERE ID = C_CallerOrg;
	SET C_FY1 = CONCAT(
					CAST(C_FiscalYear AS CHAR(4))
					,'-'
					,CAST(C_FirstMonth AS CHAR(2))
					,'-01'
				);
	SET C_FY2 =  CONCAT(
					CAST(C_FiscalYear+1 AS CHAR(4))
					,'-'
					,CAST(C_FirstMonth AS CHAR(2))
					,'-01'
				);

	SELECT 
		objtrackerT_FyCalendar.PeriodStarting AS C_PeriodStarting
		,objtrackerT_Objective.ID AS C_OID  
		,objtrackerT_Measurement.ID AS C_MID  
		,objtrackerT_Measurement.Measurement AS C_Measurement
		,objtrackerT_Target.Target AS C_Target
		,objtrackerT_Target.Target1 AS C_Target1
		,objtrackerT_Target.Target2 AS C_Target2
		,objtrackerF_Status(
			objtrackerT_Objective.MetricTypeID
			,RTRIM(LTRIM(objtrackerT_Measurement.Measurement))
			,RTRIM(LTRIM(objtrackerT_Target.Target))
			,RTRIM(LTRIM(objtrackerT_Target.Target1))
			,RTRIM(LTRIM(objtrackerT_Target.Target2))
		) AS C_Status
		,objtrackerT_Objective.FrequencyID AS C_FrequencyID
		,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
		,objtrackerT_ObjectiveType.Title AS C_Type
		,objtrackerT_Objective.Title AS C_Title
		,objtrackerT_Department.Title2 AS C_DeptTitle2
	FROM objtrackerT_Objective	
	JOIN objtrackerT_FyCalendar		ON objtrackerT_FyCalendar.FrequencyID		= objtrackerT_Objective.FrequencyID 
						   AND objtrackerT_FyCalendar.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Target			ON objtrackerT_Target.ID				= objtrackerT_Objective.ID 
						   AND objtrackerT_Target.OrganizationID	= objtrackerT_Objective.OrganizationID
						   AND objtrackerT_Target.FiscalYear 		<= objtrackerT_FyCalendar.FiscalYear	
	JOIN objtrackerT_ObjectiveType	ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID 
						   AND objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Person			ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID
						   AND objtrackerT_Person.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Department		ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
						   AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID
	LEFT OUTER 
	JOIN objtrackerT_Measurement		ON objtrackerT_Measurement.ObjectiveID	= objtrackerT_Objective.ID
						   AND objtrackerT_Measurement.OrganizationID	= objtrackerT_Objective.OrganizationID
						   AND objtrackerT_Measurement.PeriodStarting = objtrackerT_FyCalendar.PeriodStarting	
	WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg
	  AND (objtrackerT_Objective.FiscalYear1 <= C_FiscalYear AND objtrackerT_Objective.FiscalYear2 >= C_FiscalYear )
	  AND objtrackerT_FyCalendar.FiscalYear = C_FiscalYear
	ORDER BY objtrackerT_Objective.Title,objtrackerT_Objective.ID,objtrackerT_Measurement.PeriodStarting  ;
 -- exec objtrackerP_Dashboard 2010
	
END
$$
