DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveList;
DELIMITER $$
/*
  DROP TEMPORARY table TTable;
	call objtrackerP_ObjectiveList (1,1,'U1','All')
	call objtrackerP_ObjectiveList (1,1,'1','x')
	call objtrackerP_ObjectiveList (1,1,'U1','Axxll')
*/
CREATE PROCEDURE objtrackerP_ObjectiveList (
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_ID			VARCHAR(32) -- one id or id-id range
	,C_Time	  		VARCHAR(8)
)									
BEGIN	
	DECLARE C_ID1, C_ID2 VARCHAR(8);
	DECLARE C_Index INT;

	SET @FiscalYear := objtrackerF_FiscalYear(C_CallerOrg);
	CREATE TEMPORARY table TTable(ID INT);

	-- Turn two SELECTS below into one
	IF SUBSTR(C_ID,1,1) = 'U' THEN
		IF C_Time = 'All' THEN
			INSERT INTO TTable (ID) 
				SELECT objtrackerT_Objective.ID FROM objtrackerT_Objective 
				WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg
				  AND objtrackerT_Objective.OwnerID = SUBSTRING(C_ID,2,LENGTH(C_ID)-1);
		ELSE
			INSERT INTO TTable (ID) 
				SELECT objtrackerT_Objective.ID FROM objtrackerT_Objective 
				WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg
				  AND objtrackerT_Objective.OwnerID =  SUBSTRING(C_ID,2,LENGTH(C_ID)-1) 
				  AND objtrackerT_Objective.FiscalYear1 <= @FiscalYear 
				  AND objtrackerT_Objective.FiscalYear2 >= @FiscalYear;
		END IF;
	ELSE
 		SET C_Index := LOCATE('-',C_ID);
		IF C_Index = 0 THEN 
			SET C_ID1 = CAST(C_ID AS UNSIGNED INTEGER);
			SET C_ID2 = C_ID1;
		ELSE
			SET C_ID1 = SUBSTR(C_ID,1,C_Index-1);
			SET C_ID2 = SUBSTR(C_ID,C_Index+1,LENGTH(C_ID)-C_Index);
		END IF;

		IF C_Time = 'All' THEN
			INSERT INTO TTable (ID) 
				SELECT objtrackerT_Objective.ID FROM objtrackerT_Objective
				JOIN objtrackerT_Person			ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID
				 						AND objtrackerT_Person.OrganizationID = objtrackerT_Objective.OrganizationID
				JOIN objtrackerT_Department		ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
										AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID
				WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg
				  AND objtrackerT_Person.DepartmentID >= C_ID1
				  AND objtrackerT_Person.DepartmentID <= C_ID2 ;
		ELSE
			INSERT INTO TTable (ID) 
				SELECT objtrackerT_Objective.ID FROM objtrackerT_Objective
				JOIN objtrackerT_Person			ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID
				 						AND objtrackerT_Person.OrganizationID = objtrackerT_Objective.OrganizationID
				JOIN objtrackerT_Department		ON objtrackerT_Department.ID			= objtrackerT_Person.DepartmentID
				 						AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID
				WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg
				  AND objtrackerT_Person.DepartmentID >= C_ID1 
				  AND objtrackerT_Person.DepartmentID <= C_ID2 
				  AND objtrackerT_Objective.FiscalYear1 <= @FiscalYear
				  AND objtrackerT_Objective.FiscalYear2 >= @FiscalYear;
		END IF;
	END IF;
 	
	-- Single SELECT based on prior 
	SELECT 
		objtrackerT_Objective.ID AS C_ID
		,(SELECT COUNT(*) FROM objtrackerT_Measurement WHERE objtrackerT_Measurement.ObjectiveID = objtrackerT_Objective.ID) AS C_Usage
		,objtrackerT_Objective.Title AS C_Title
		,objtrackerT_Frequency.Title AS C_Frequency
		,objtrackerT_Objective.Description AS C_Description
		,objtrackerT_Objective.Source AS C_Source
		,CASE WHEN objtrackerT_Objective.IsPublic = 1 THEN 'Public' ELSE 'Private' END AS C_IsPublic
		,objtrackerT_Objective.MetricTypeID AS C_MetricTypeID
		,objtrackerT_Objective.TypeID AS C_TypeID
		,FY1.Title AS C_FY1Title
		,FY2.Title AS C_FY2Title
		,objtrackerT_ObjectiveType.Title AS C_ObjType
		,CASE WHEN objtrackerT_Person.Active = 1 THEN objtrackerT_Person.FullName ELSE CONCAT('Inactive-',objtrackerT_Person.FullName) END AS C_Owner
		,objtrackerT_Department.Title AS C_DeptTitle
		,objtrackerT_Department.Title2 AS C_DeptTitle2	
		,objtrackerF_FormatDate(objtrackerT_Objective.Track_Changed) AS C_Track_Changed
		,objtrackerF_FormatSortedDate(objtrackerT_Objective.Track_Changed) AS C_Track_SortedChanged
		,objtrackerT_Objective.Track_Userid AS C_Track_Userid
	FROM objtrackerT_Objective 
	JOIN objtrackerT_ObjectiveType	ON objtrackerT_ObjectiveType.ID				= objtrackerT_Objective.TypeID
						   AND objtrackerT_ObjectiveType.OrganizationID 	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Person			ON objtrackerT_Person.ID				= objtrackerT_Objective.OwnerID
 						   AND objtrackerT_Person.OrganizationID 	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_Department		ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
				 		   AND objtrackerT_Department.OrganizationID	= objtrackerT_Person.OrganizationID
	JOIN objtrackerT_Frequency		ON objtrackerT_Frequency.ID				= objtrackerT_Objective.FrequencyID
				 		   AND objtrackerT_Frequency.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FiscalYear AS FY1 ON FY1.ID				= objtrackerT_Objective.FiscalYear1
							AND FY1.OrganizationID	= objtrackerT_Objective.OrganizationID
	JOIN objtrackerT_FiscalYear AS FY2 ON FY2.ID				= objtrackerT_Objective.FiscalYear2
							AND FY2.OrganizationID	= objtrackerT_Objective.OrganizationID
	WHERE objtrackerT_Objective.OrganizationID = C_CallerOrg
	  AND objtrackerT_Objective.ID IN (SELECT ID FROM TTable)
	ORDER BY objtrackerT_Objective.Title
	;
	
	DROP TEMPORARY table TTable;
END
$$
