DROP PROCEDURE IF EXISTS objtrackerP_ReportMeasurements;
DELIMITER $$
/*
		call objtrackerP_ReportMeasurements(1,1,'U1','All')   --  department 1 as in U1 all fy
		call objtrackerP_ReportMeasurements(1,1,'U1','Current') --department 1 as in U1 this fy
		call objtrackerP_ReportMeasurements(1,1,'0-99999','All') -- all users all fy
		call objtrackerP_ReportMeasurements(1,1,'0-99999','Current') -- all users this fy
*/
CREATE PROCEDURE objtrackerP_ReportMeasurements (
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_DeptID		VARCHAR(8)		-- range of departments
	,C_Time			VARCHAR(8)		-- All or Current
)
BEGIN
	DECLARE C_ID1, C_ID2 VARCHAR(8);
	DECLARE C_Index INT;
	DECLARE C_FiscalYear INT;
	SET @FiscalYear := objtrackerF_FiscalYear(C_CallerOrg);

	IF SUBSTR(C_DeptID,1,1) = 'U' THEN
		IF C_Time = 'All' THEN
	  		SELECT 
				objtrackerT_Objective.ID AS C_OID
				,FY.Title AS C_FiscalYear
				,objtrackerT_FyCalendar.PeriodStarting AS C_PeriodStarting
				,objtrackerT_Measurement.ID AS C_MID
				,objtrackerT_Measurement.Measurement   AS C_Measurement
				,objtrackerT_Measurement.Notes   AS C_Notes
				,objtrackerT_Objective.Description AS C_Description
				,objtrackerT_Objective.Source AS C_Source
				,objtrackerT_Objective.FiscalYear1 AS C_FiscalYear1
				,objtrackerT_Objective.FiscalYear2 AS C_FiscalYear2
				,objtrackerT_Objective.FrequencyID AS C_FrequencyID
				,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
				,objtrackerT_Objective.Title AS C_Title
				,CASE WHEN objtrackerT_Objective.IsPublic = 1 THEN 'Public' ELSE 'Private' END AS C_IsPublic
				,Case WHEN objtrackerT_Person.Active = 1 THEN objtrackerT_Person.FullName ELSE CONCAT('Inactive-',objtrackerT_Person.FullName) END AS C_FullName
 				,objtrackerT_Department.Title2 AS C_DeptTitle2
				,objtrackerT_ObjectiveType.Title AS C_Type
				,objtrackerT_Frequency.Title AS C_Frequency
				,FY1.Title AS C_FY1Title
				,FY2.Title AS C_FY2Title
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
			JOIN objtrackerT_Person		ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID 
							   AND objtrackerT_Person.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Department	ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID 
							   AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID 
			JOIN objtrackerT_FyCalendar	ON objtrackerT_FyCalendar.FrequencyID		= objtrackerT_Objective.FrequencyID 
							   AND objtrackerT_FyCalendar.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Target		ON objtrackerT_Target.ID					= objtrackerT_Objective.ID 
							   AND objtrackerT_Target.OrganizationID		= objtrackerT_Objective.OrganizationID 
							   AND objtrackerT_Target.FiscalYear			= objtrackerT_FyCalendar.FiscalYear	
			JOIN objtrackerT_ObjectiveType 	ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID 
								   AND objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Frequency	ON objtrackerT_Frequency.ID				= objtrackerT_Objective.FrequencyID
							   AND objtrackerT_Frequency.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY1 	ON FY1.ID				= objtrackerT_Objective.FiscalYear1
									   AND FY1.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY2	ON FY2.ID				= objtrackerT_Objective.FiscalYear2
									   AND FY2.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY	ON FY.ID				= objtrackerT_FyCalendar.FiscalYear
								   AND FY.OrganizationID	= objtrackerT_Objective.OrganizationID 
			LEFT OUTER 
			JOIN objtrackerT_Measurement	ON objtrackerT_Measurement.ObjectiveID	= objtrackerT_Objective.ID
							   AND objtrackerT_Measurement.OrganizationID	= objtrackerT_Objective.OrganizationID 
							   AND objtrackerT_Measurement.PeriodStarting = objtrackerT_FyCalendar.PeriodStarting	
			WHERE objtrackerT_Objective.OrganizationID 	= C_CallerOrg
			  AND objtrackerT_Objective.OwnerID  			= SUBSTR(C_DeptID,2,LENGTH(C_DeptID))
			  AND objtrackerT_FyCalendar.PeriodStarting	< Current_Timestamp	
			ORDER BY objtrackerT_Objective.Title,objtrackerT_Objective.ID,objtrackerT_FyCalendar.PeriodStarting ;
		ELSE -- U but not ALL
			SELECT 
	  			objtrackerT_Objective.ID AS C_OID
				,FY.Title AS C_FiscalYear
				,objtrackerT_FyCalendar.PeriodStarting AS C_PeriodStarting
				,objtrackerT_Measurement.ID AS C_MID
				,objtrackerT_Measurement.Measurement   AS C_Measurement
				,objtrackerT_Measurement.Notes   AS C_Notes
				,objtrackerT_Objective.Description AS C_Description
				,objtrackerT_Objective.Source AS C_Source
				,objtrackerT_Objective.FiscalYear1 AS C_FiscalYear1
				,objtrackerT_Objective.FiscalYear2 AS C_FiscalYear2
				,objtrackerT_Objective.FrequencyID AS C_FrequencyID
				,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
				,objtrackerT_Objective.Title AS C_Title
				,CASE WHEN objtrackerT_Objective.IsPublic = 1 THEN 'Public' ELSE 'Private' END AS C_IsPublic
	  			,Case WHEN objtrackerT_Person.Active = 1 THEN objtrackerT_Person.FullName ELSE CONCAT('Inactive-',objtrackerT_Person.FullName) END AS C_FullName
 				,objtrackerT_Department.Title2 AS C_DeptTitle2
				,objtrackerT_ObjectiveType.Title AS C_Type
				,objtrackerT_Frequency.Title AS C_Frequency
				,FY1.Title AS C_FY1Title
				,FY2.Title AS C_FY2Title
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
			JOIN objtrackerT_Person		ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID 
							   AND objtrackerT_Person.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Department	ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID 
							   AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID 
			JOIN objtrackerT_FyCalendar	ON objtrackerT_FyCalendar.FrequencyID		= objtrackerT_Objective.FrequencyID 
							   AND objtrackerT_FyCalendar.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Target		ON objtrackerT_Target.ID					= objtrackerT_Objective.ID 
							   AND objtrackerT_Target.OrganizationID		= objtrackerT_Objective.OrganizationID 
							   AND objtrackerT_Target.FiscalYear			= objtrackerT_FyCalendar.FiscalYear	
			JOIN objtrackerT_ObjectiveType 	ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID 
								   AND objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Frequency	ON objtrackerT_Frequency.ID				= objtrackerT_Objective.FrequencyID
							   AND objtrackerT_Frequency.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY1 	ON FY1.ID				= objtrackerT_Objective.FiscalYear1
									   AND FY1.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY2	ON FY2.ID				= objtrackerT_Objective.FiscalYear2
									   AND FY2.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY	ON FY.ID				= objtrackerT_FyCalendar.FiscalYear
								   AND FY.OrganizationID	= objtrackerT_Objective.OrganizationID 
			LEFT OUTER 
			JOIN objtrackerT_Measurement	ON objtrackerT_Measurement.ObjectiveID	= objtrackerT_Objective.ID
							   AND objtrackerT_Measurement.OrganizationID	= objtrackerT_Objective.OrganizationID 
							   AND objtrackerT_Measurement.PeriodStarting = objtrackerT_FyCalendar.PeriodStarting	
			WHERE objtrackerT_Objective.OrganizationID 	= C_CallerOrg
			  AND objtrackerT_Objective.OwnerID  			= SUBSTR(C_DeptID,2,LENGTH(C_DeptID))
			  AND objtrackerT_Objective.FiscalYear1 		<= @FiscalYear
			  AND objtrackerT_Objective.FiscalYear2 		>= @FiscalYear 
			  AND objtrackerT_FyCalendar.PeriodStarting	< Current_Timestamp	
			ORDER BY objtrackerT_Objective.Title,objtrackerT_Objective.ID,objtrackerT_FyCalendar.PeriodStarting ;
		END IF;
	ELSE -- not U*
		SET C_Index := LOCATE('-',C_DeptID);
		IF C_Index = 0 THEN 
			SET C_ID1 = CAST(C_DeptID AS UNSIGNED INTEGER);
			SET C_ID2 = C_ID1;
		ELSE
			SET C_ID1 = SUBSTR(C_DeptID,1,C_Index-1);
			SET C_ID2 = SUBSTR(C_DeptID,C_Index+1,LENGTH(C_DeptID)-C_Index);
		END IF;

		IF C_Time = 'All' THEN
			SELECT 
				objtrackerT_Objective.ID AS C_OID
				,FY.Title AS C_FiscalYear
				,objtrackerT_FyCalendar.PeriodStarting AS C_PeriodStarting
				,objtrackerT_Measurement.ID AS C_MID
				,objtrackerT_Measurement.Measurement   AS C_Measurement
				,objtrackerT_Measurement.Notes   AS C_Notes
				,objtrackerT_Objective.Description  AS C_Description
				,objtrackerT_Objective.Source AS C_Source
				,objtrackerT_Objective.FiscalYear1 AS C_FiscalYear1
				,objtrackerT_Objective.FiscalYear2 AS C_FiscalYear2
				,objtrackerT_Objective.FrequencyID AS C_FrequencyID
				,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
				,objtrackerT_Objective.Title AS C_Title
				,CASE WHEN objtrackerT_Objective.IsPublic = 1 THEN 'Public' ELSE 'Private' END AS C_IsPublic
				,Case WHEN objtrackerT_Person.Active = 1 THEN objtrackerT_Person.FullName ELSE CONCAT('Inactive-',objtrackerT_Person.FullName) END AS C_FullName
				,objtrackerT_Department.Title2 AS C_DeptTitle2
				,objtrackerT_ObjectiveType.Title AS C_Type
				,objtrackerT_Frequency.Title AS C_Frequency
				,FY1.Title AS C_FY1Title
				,FY2.Title AS C_FY2Title
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
			JOIN objtrackerT_Person		ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID 
							   AND objtrackerT_Person.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Department	ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID 
							   AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID 
			JOIN objtrackerT_FyCalendar	ON objtrackerT_FyCalendar.FrequencyID		= objtrackerT_Objective.FrequencyID 
							   AND objtrackerT_FyCalendar.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Target		ON objtrackerT_Target.ID					= objtrackerT_Objective.ID 
							   AND objtrackerT_Target.OrganizationID		= objtrackerT_Objective.OrganizationID 
							   AND objtrackerT_Target.FiscalYear			= objtrackerT_FyCalendar.FiscalYear	
			JOIN objtrackerT_ObjectiveType 	ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID 
								   AND objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Frequency	ON objtrackerT_Frequency.ID				= objtrackerT_Objective.FrequencyID
							   AND objtrackerT_Frequency.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY1 	ON FY1.ID				= objtrackerT_Objective.FiscalYear1
									   AND FY1.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY2	ON FY2.ID				= objtrackerT_Objective.FiscalYear2
									   AND FY2.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY	ON FY.ID				= objtrackerT_FyCalendar.FiscalYear
								   AND FY.OrganizationID	= objtrackerT_Objective.OrganizationID 
			LEFT OUTER 
			JOIN objtrackerT_Measurement	ON objtrackerT_Measurement.ObjectiveID	= objtrackerT_Objective.ID
							   AND objtrackerT_Measurement.OrganizationID	= objtrackerT_Objective.OrganizationID 
							   AND objtrackerT_Measurement.PeriodStarting = objtrackerT_FyCalendar.PeriodStarting	
			WHERE objtrackerT_Objective.OrganizationID 	= C_CallerOrg
			  AND objtrackerT_Person.DepartmentID >= C_ID1
			  AND objtrackerT_Person.DepartmentID <= C_ID2
			  AND objtrackerT_FyCalendar.PeriodStarting	< Current_Timestamp	
			ORDER BY objtrackerT_Objective.Title,objtrackerT_Objective.ID,objtrackerT_FyCalendar.PeriodStarting ;
		ELSE -- not U* and not All
			SELECT 
				objtrackerT_Objective.ID AS C_OID
				,FY.Title AS C_FiscalYear
				,objtrackerT_FyCalendar.PeriodStarting AS C_PeriodStarting
				,objtrackerT_Measurement.ID AS C_MID
				,objtrackerT_Measurement.Measurement   AS C_Measurement
				,objtrackerT_Measurement.Notes   AS C_Notes
				,objtrackerT_Objective.Description AS C_Description
				,objtrackerT_Objective.Source AS C_Source
				,objtrackerT_Objective.FiscalYear1 AS C_FiscalYear1
				,objtrackerT_Objective.FiscalYear2 AS C_FiscalYear2
				,objtrackerT_Objective.FrequencyID AS C_FrequencyID
				,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
				,objtrackerT_Objective.Title AS C_Title
				,CASE WHEN objtrackerT_Objective.IsPublic = 1 THEN 'Public' ELSE 'Private' END AS C_IsPublic
				,Case WHEN objtrackerT_Person.Active = 1 THEN objtrackerT_Person.FullName ELSE CONCAT('Inactive-',objtrackerT_Person.FullName) END AS C_FullName
				,objtrackerT_Department.Title2 AS C_DeptTitle2
				,objtrackerT_ObjectiveType.Title AS C_Type
				,objtrackerT_Frequency.Title AS C_Frequency
				,FY1.Title AS C_FY1Title
				,FY2.Title AS C_FY2Title
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
			JOIN objtrackerT_Person		ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID 
							   AND objtrackerT_Person.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Department	ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID 
							   AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID 
			JOIN objtrackerT_FyCalendar	ON objtrackerT_FyCalendar.FrequencyID		= objtrackerT_Objective.FrequencyID 
							   AND objtrackerT_FyCalendar.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Target		ON objtrackerT_Target.ID					= objtrackerT_Objective.ID 
							   AND objtrackerT_Target.OrganizationID		= objtrackerT_Objective.OrganizationID 
							   AND objtrackerT_Target.FiscalYear			= objtrackerT_FyCalendar.FiscalYear	
			JOIN objtrackerT_ObjectiveType 	ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID 
								   AND objtrackerT_ObjectiveType.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_Frequency	ON objtrackerT_Frequency.ID				= objtrackerT_Objective.FrequencyID
							   AND objtrackerT_Frequency.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY1 	ON FY1.ID				= objtrackerT_Objective.FiscalYear1
									   AND FY1.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY2	ON FY2.ID				= objtrackerT_Objective.FiscalYear2
									   AND FY2.OrganizationID	= objtrackerT_Objective.OrganizationID 
			JOIN objtrackerT_FiscalYear AS FY	ON FY.ID				= objtrackerT_FyCalendar.FiscalYear
								   AND FY.OrganizationID	= objtrackerT_Objective.OrganizationID 
			LEFT OUTER 
			JOIN objtrackerT_Measurement	ON objtrackerT_Measurement.ObjectiveID	= objtrackerT_Objective.ID
							   AND objtrackerT_Measurement.OrganizationID	= objtrackerT_Objective.OrganizationID 
							   AND objtrackerT_Measurement.PeriodStarting = objtrackerT_FyCalendar.PeriodStarting	
			WHERE objtrackerT_Objective.OrganizationID 	= C_CallerOrg
			  AND objtrackerT_Person.DepartmentID >= C_ID1
			  AND objtrackerT_Person.DepartmentID <= C_ID2
			  AND objtrackerT_Objective.FiscalYear1 <= @FiscalYear
			  AND objtrackerT_Objective.FiscalYear2 >= @FiscalYear
			  AND objtrackerT_FyCalendar.PeriodStarting	< Current_Timestamp	
			ORDER BY objtrackerT_Objective.Title,objtrackerT_Objective.ID,objtrackerT_FyCalendar.PeriodStarting ;
		END IF;
	END IF;
END
$$
