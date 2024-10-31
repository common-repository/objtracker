DROP PROCEDURE IF EXISTS objtrackerP_MeasurementList;
DELIMITER $$
/*
  CALL objtrackerP_MeasurementList (1,1,1)
  SELECT * FROM objtrackerT_Measurement
  DELETE FROM objtrackerT_Measurement WHERE ID = 1
*/
CREATE PROCEDURE objtrackerP_MeasurementList(
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_ID			INT  
)
BEGIN
	SELECT 
		objtrackerT_Measurement.ID AS C_ID
		,objtrackerT_Measurement.ObjectiveID AS C_ObjectiveID
		,objtrackerT_Measurement.PeriodStarting AS C_PeriodStarting
		,CONCAT( objtrackerT_Measurement.ObjectiveID, '.',Date_Format(objtrackerT_Measurement.PeriodStarting, '%Y-%m-%d') ) 
			AS C_Docs1Key
		,objtrackerF_FormatSortedDate(objtrackerT_Measurement.PeriodStarting) AS C_SortedPeriodStarting
		,(SELECT COUNT(*) FROM objtrackerT_Documentation 
				WHERE OrganizationID = C_CallerOrg
					AND objtrackerT_Documentation.ObjectiveID = objtrackerT_Objective.ID
					AND objtrackerT_Documentation.PeriodStarting = objtrackerT_Measurement.PeriodStarting) AS C_Docs
		,objtrackerT_Measurement.Measurement AS C_Measurement
		,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
		,objtrackerT_FyCalendar.FiscalYear AS C_FiscalYear
		,objtrackerT_Measurement.Notes AS C_Notes
		,objtrackerF_Status(
			objtrackerT_Objective.MetricTypeID
			,RTRIM(LTRIM(objtrackerT_Measurement.Measurement))
			,RTRIM(LTRIM(objtrackerT_Target.Target))
			,RTRIM(LTRIM(objtrackerT_Target.Target1))
			,RTRIM(LTRIM(objtrackerT_Target.Target2))
		) AS C_Status
		,objtrackerF_FormatDate(objtrackerT_Measurement.Track_Changed) AS C_Track_Changed
		,objtrackerF_FormatSortedDate(objtrackerT_Measurement.Track_Changed) AS C_Track_SortedChanged
		,objtrackerT_Measurement.Track_Userid  AS C_Track_Userid
	FROM objtrackerT_Objective
	JOIN objtrackerT_Measurement		ON objtrackerT_Measurement.ObjectiveID	= objtrackerT_Objective.ID
						   AND objtrackerT_Measurement.OrganizationID = objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FyCalendar		ON objtrackerT_FyCalendar.FrequencyID		= objtrackerT_Objective.FrequencyID 
						   AND objtrackerT_FyCalendar.OrganizationID = objtrackerT_Measurement.OrganizationID
						   AND objtrackerT_FyCalendar.PeriodStarting = objtrackerT_Measurement.PeriodStarting
	JOIN objtrackerT_Target		  	ON objtrackerT_Target.ID	= objtrackerT_Objective.ID 
						   AND objtrackerT_Target.FiscalYear = objtrackerT_FyCalendar.FiscalYear
						   AND objtrackerT_Target.OrganizationID = objtrackerT_FyCalendar.OrganizationID
	WHERE objtrackerT_Measurement.ObjectiveID = C_ID
	  AND objtrackerT_Measurement.OrganizationID = C_CallerOrg 
	ORDER BY objtrackerT_Measurement.PeriodStarting;
END
$$
