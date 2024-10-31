DROP PROCEDURE IF EXISTS objtrackerP_Extract;
DELIMITER $$
CREATE PROCEDURE objtrackerP_Extract(
	C_CallerOrg				INT 
	,C_CallerUser			INT 
)
BEGIN
	SELECT 
		OrganizationID, FirstMonth 
		FROM objtrackerT_Organization ; 
	SELECT 
		OrganizationID, ID,ParentID,Active,Title,Title2 
		FROM objtrackerT_Department ORDER BY ID;
	SELECT 
		OrganizationID, ID,DepartmentID,Active,UserName,FullName 
		FROM objtrackerT_Person ORDER BY ID;
	SELECT 
		OrganizationID, ID, Title 
		FROM objtrackerT_FiscalYear ORDER BY ID;
	SELECT 
		OrganizationID, FiscalYear,FrequencyID,PeriodStarting
		FROM objtrackerT_FyCalendar ORDER BY FiscalYear,FrequencyID,PeriodStarting;
	SELECT 
		OrganizationID, ID,FiscalYear1,FiscalYear2,OwnerID,IsPublic,Source,TypeID,FrequencyID,MetricTypeID,Title,Description
		FROM objtrackerT_Objective ORDER BY ID;
	SELECT 
		OrganizationID, ID,FiscalYear,Target,Target1,Target2
		FROM objtrackerT_Target ORDER BY ID;
	SELECT 
		OrganizationID, ID,ObjectiveID,PeriodStarting,Measurement,Notes 
		FROM objtrackerT_Measurement ORDER BY ObjectiveID,PeriodStarting;
	
END
$$
