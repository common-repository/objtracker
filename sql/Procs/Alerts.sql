DROP PROCEDURE IF EXISTS objtrackerP_Alerts;
DELIMITER $$
/*
	Revised: 2012/12/03 Alerts was allowed future periods to be added.
*/
CREATE PROCEDURE objtrackerP_Alerts (
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_Userid		INT		-- 0 all users, otherwise a specific user
)
BEGIN
  DECLARE C_FiscalYear INT;
	CREATE TEMPORARY TABLE TTable(ID INT);
 	SELECT objtrackerF_FiscalYear(C_CallerOrg) INTO C_FiscalYear;
	-- Turn two SELECTS below into one
	IF C_Userid = 0 THEN
		INSERT INTO TTable (ID) SELECT ID FROM objtrackerT_Objective WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg;
	ELSE
		INSERT INTO TTable (ID) SELECT ID FROM objtrackerT_Objective WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg AND OwnerID = C_userID;
	END IF;
	SELECT 
		objtrackerT_Objective.ID AS C_OID
		,objtrackerT_FyCalendar.FiscalYear AS C_FiscalYear
		,objtrackerT_FyCalendar.PeriodStarting AS C_PeriodStarting
		,objtrackerT_Measurement.ID AS C_MID
		,objtrackerT_Measurement.Measurement AS C_Measurement
		,objtrackerT_Measurement.Notes AS C_Notes
		,CASE WHEN objtrackerT_Measurement.ID IS NULL THEN 'Add' ELSE 'Revise' END AS C_Action
		,objtrackerT_Objective.FiscalYear1 AS C_FiscalYear1
		,objtrackerT_Objective.FiscalYear2 AS C_FiscalYear2
		,objtrackerT_Objective.FrequencyID AS C_FrequencyID
		,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
		,objtrackerT_Objective.Title AS C_Title
		,objtrackerT_ObjectiveType.Title AS C_Type
		,objtrackerT_Frequency.Title AS C_Frequency
		,FY1.Title AS C_Fy1Title
		,FY2.Title AS C_Fy2Title
		,objtrackerF_Status(
			objtrackerT_Objective.MetricTypeID
			,RTRIM(LTRIM(objtrackerT_Measurement.Measurement))
			,RTRIM(LTRIM(objtrackerT_Target.Target))
			,RTRIM(LTRIM(objtrackerT_Target.Target1))
			,RTRIM(LTRIM(objtrackerT_Target.Target2))
		) AS C_Status
		,objtrackerT_Target.Target AS C_Target
		,objtrackerT_Target.Target1 AS C_Target1
		,objtrackerT_Target.Target2 AS C_Target2
	FROM objtrackerT_Objective
	JOIN objtrackerT_FyCalendar		 ON objtrackerT_FyCalendar.FrequencyID		= objtrackerT_Objective.FrequencyID
						    AND objtrackerT_FyCalendar.OrganizationID		= objtrackerT_Objective.OrganizationID
							AND objtrackerT_FyCalendar.FiscalYear			>= objtrackerT_Objective.FiscalYear1
							AND objtrackerT_FyCalendar.FiscalYear			<= objtrackerT_Objective.FiscalYear2
	JOIN objtrackerT_Target			 ON objtrackerT_Target.ID						= objtrackerT_Objective.ID
						    AND objtrackerT_Target.OrganizationID			= objtrackerT_Objective.OrganizationID
							AND objtrackerT_Target.FiscalYear				= objtrackerT_FyCalendar.FiscalYear
	JOIN objtrackerT_ObjectiveType	 ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID
						    AND objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Frequency		 ON objtrackerT_Frequency.ID					= objtrackerT_Objective.FrequencyID
						    AND objtrackerT_Frequency.OrganizationID		= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FiscalYear AS	FY1	ON FY1.ID					= objtrackerT_Objective.FiscalYear1
							   AND FY1.OrganizationID		= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FiscalYear AS	FY2	ON FY2.ID					= objtrackerT_Objective.FiscalYear2
							   AND FY2.OrganizationID		= objtrackerT_Objective.OrganizationID
	LEFT OUTER
	JOIN objtrackerT_Measurement		 ON objtrackerT_Measurement.ObjectiveID		= objtrackerT_Objective.ID
						    AND objtrackerT_Objective.OrganizationID		= objtrackerT_Measurement.OrganizationID
							AND objtrackerT_Measurement.PeriodStarting	= objtrackerT_FyCalendar.PeriodStarting
	WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg AND objtrackerT_Objective.ID IN (SELECT ID FROM TTable)
	AND objtrackerT_Objective.FiscalYear1 <= C_FiscalYear
	AND objtrackerT_Objective.FiscalYear2 >= C_FiscalYear
	AND objtrackerT_FyCalendar.PeriodStarting < Current_Timestamp	
	ORDER BY objtrackerT_Objective.Title,objtrackerT_Objective.ID,objtrackerT_FyCalendar.PeriodStarting 
	;
  DROP TEMPORARY TABLE TTable;
END
$$
