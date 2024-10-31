DROP PROCEDURE IF EXISTS objtrackerP_MeasurementsMissing;
DELIMITER $$
/*
	Revised Added: 2012/12/03 Allows Objective to list missing measurements
	call objtrackerP_MeasurementsMissing(13)
*/
CREATE PROCEDURE objtrackerP_MeasurementsMissing (
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_ID			INT  
)
BEGIN
   	SELECT 
		objtrackerT_FyCalendar.PeriodStarting AS C_PeriodStarting
 		,objtrackerF_FormatSortedDate(objtrackerT_FyCalendar.PeriodStarting) AS C_PeriodSorted
		,objtrackerF_FormatDate(objtrackerT_FyCalendar.PeriodStarting) AS C_Period
	FROM objtrackerT_Objective	
	JOIN objtrackerT_FyCalendar		 ON objtrackerT_FyCalendar.FrequencyID	= objtrackerT_Objective.FrequencyID 
							AND objtrackerT_FyCalendar.OrganizationID	= objtrackerT_Objective.OrganizationID	
							AND objtrackerT_FyCalendar.FiscalYear		>= objtrackerT_Objective.FiscalYear1	
							AND objtrackerT_FyCalendar.FiscalYear		<= objtrackerT_Objective.FiscalYear2	
	JOIN objtrackerT_Target			 ON objtrackerT_Target.ID					= objtrackerT_Objective.ID 
							AND objtrackerT_Target.OrganizationID	= objtrackerT_Objective.OrganizationID	
							AND objtrackerT_Target.FiscalYear		= objtrackerT_FyCalendar.FiscalYear	
	JOIN objtrackerT_ObjectiveType	 ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID 
							AND objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID	
	LEFT OUTER 
	JOIN objtrackerT_Measurement		 ON objtrackerT_Measurement.ObjectiveID		= objtrackerT_Objective.ID
							AND objtrackerT_Measurement.OrganizationID	= objtrackerT_FyCalendar.OrganizationID	
							AND objtrackerT_Measurement.PeriodStarting	= objtrackerT_FyCalendar.PeriodStarting	
	WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg
		AND objtrackerT_Objective.ID = C_ID
		AND objtrackerT_FyCalendar.PeriodStarting	< Now()	
		AND objtrackerT_Measurement.ID is null
	ORDER BY objtrackerT_FyCalendar.PeriodStarting ;
END
$$
