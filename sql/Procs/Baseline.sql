DROP PROCEDURE IF EXISTS objtrackerP_Baseline;
DELIMITER $$
 -- Call objtrackerP_Baseline
CREATE PROCEDURE objtrackerP_Baseline (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
)
BEGIN
	-- Get table for finding the maximum target value for objective
  DROP TABLE IF EXISTS C_MaxFyTable;
  CREATE TEMPORARY TABLE C_MaxFyTable 	(mftID INT, mftFY INT);
  INSERT INTO C_MaxFyTable (mftID,mftFY) 
	(SELECT ID,Max(FiscalYear) FROM objtrackerT_Target WHERE OrganizationID = C_CallerOrg GROUP BY ID);

  DROP TABLE IF EXISTS C_MaxTargetTable;
  CREATE TEMPORARY TABLE C_MaxTargetTable (mttID INT, mttTarget VARCHAR(32)	);
  
	INSERT INTO C_MaxTargetTable (mttID,mttTarget) ( 
		SELECT objtrackerT_Objective.ID, objtrackerT_Target.Target
		FROM objtrackerT_Objective 
		JOIN C_MaxFyTable ON objtrackerT_Objective.ID	= mftID
		JOIN objtrackerT_Target	ON objtrackerT_Objective.ID	= objtrackerT_Target.ID AND mftFY	= objtrackerT_Target.FiscalYear
		WHERE objtrackerT_Objective.OrganizationID 	= C_CallerOrg
	);
	SELECT 
		objtrackerT_Objective.ID AS C_OID
		,objtrackerT_FiscalYear.Title  AS C_FiscalYear
		,objtrackerT_FyCalendar.PeriodStarting   AS C_PeriodStarting
		,objtrackerT_Measurement.ID AS C_MID
		,objtrackerT_Objective.Title AS C_Title
		,objtrackerT_Measurement.Measurement AS C_Measurement 
		,objtrackerT_Objective.FrequencyID  AS C_FrequencyID
		,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
		,objtrackerF_Status(
			objtrackerT_Objective.MetricTypeID
			,RTRIM(LTRIM(objtrackerT_Measurement.Measurement))
			,RTRIM(LTRIM(objtrackerT_Target.Target))
			,RTRIM(LTRIM(objtrackerT_Target.Target1))
			,RTRIM(LTRIM(objtrackerT_Target.Target2))
		) AS C_Status
		,mttTarget AS C_mttTarget
		,objtrackerT_Target.Target AS C_Target
	,objtrackerT_Target.Target1 AS C_Target1
	,objtrackerT_Target.Target2 AS C_Target2
		,objtrackerT_Department.Title2 AS C_DeptTitle2
	FROM objtrackerT_Objective 
		JOIN objtrackerT_ObjectiveType		ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID
								   AND objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID
		JOIN objtrackerT_Person				ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID
								   AND objtrackerT_Person.OrganizationID	= objtrackerT_Objective.OrganizationID
		JOIN objtrackerT_Department			ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
								   AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID
		JOIN objtrackerT_FyCalendar			ON objtrackerT_FyCalendar.FrequencyID		= objtrackerT_Objective.FrequencyID 
								   AND objtrackerT_FyCalendar.OrganizationID	= objtrackerT_Objective.OrganizationID
		JOIN objtrackerT_FiscalYear			ON objtrackerT_FiscalYear.ID 				= objtrackerT_FyCalendar.FiscalYear
								   AND objtrackerT_FiscalYear.OrganizationID	= objtrackerT_FyCalendar.OrganizationID
		JOIN objtrackerT_Target				ON objtrackerT_Target.ID				= objtrackerT_Objective.ID
								   AND objtrackerT_Target.OrganizationID	= objtrackerT_Objective.OrganizationID
								   AND objtrackerT_FyCalendar.FiscalYear	= objtrackerT_Target.FiscalYear
		JOIN C_MaxTargetTable			ON mttID						= objtrackerT_Objective.ID
		LEFT OUTER JOIN objtrackerT_Measurement	ON objtrackerT_Measurement.ObjectiveID	= objtrackerT_Objective.ID
								   	   AND objtrackerT_Measurement.OrganizationID	= objtrackerT_Objective.OrganizationID
									   AND objtrackerT_Measurement.PeriodStarting	= objtrackerT_FyCalendar.PeriodStarting
	WHERE objtrackerT_Objective.OrganizationID 	= C_CallerOrg
	  AND objtrackerT_FyCalendar.PeriodStarting < Current_Timestamp	
	ORDER BY objtrackerT_Objective.Title,objtrackerT_Objective.ID,objtrackerT_FyCalendar.PeriodStarting ; 
	
END
$$

