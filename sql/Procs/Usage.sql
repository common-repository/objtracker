DROP PROCEDURE IF EXISTS objtrackerP_Usage;
DELIMITER $$
/* 
	call objtrackerP_Usage (1,1,'objtrackerT_Person','DepartmentID','1')
	call objtrackerP_Usage (1,1,'objtrackerT_Objective','FrequencyID','A')
	call objtrackerP_Usage (1,1,'objtrackerT_Objective','MetricTypeID','I')
	call objtrackerP_Usage (1,1,'objtrackerT_Objective','TypeID','C')
	call objtrackerP_Usage (1,1,'objtrackerT_Objective','OwnerID','1')
*/
CREATE PROCEDURE objtrackerP_Usage(
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_Table		VARCHAR(32)
	,C_Column 		VARCHAR(32) 
	,C_Value 		VARCHAR(8) 
)
BEGIN
	IF LENGTH(C_Table) < 2  OR SUBSTR(C_TABLE,1,1) != 'T' OR SUBSTR(C_TABLE,2,1) != '_' THEN
		SELECT 'Invalid parms for objtrackerP_Usage.sql';
	ELSE
		SET @Table := SUBSTR(C_Table,3,LENGTH(C_Table)-2);

		IF @Table = 'Person' AND C_Column = 'DepartmentID' THEN
			SELECT 
				objtrackerT_Person.ID AS C_ID
				,objtrackerT_Person.Active AS C_Active
				,Case WHEN objtrackerT_Person.Admin = 1 THEN 'Yes' ELSE 'No' END AS C_IsAdmin
				,Case WHEN objtrackerT_Person.Active = 1 THEN 'Yes' ELSE 'No' END AS C_IsActive
				,objtrackerT_Person.FullName AS C_FullName
				,objtrackerT_Person.UserName AS C_UserName
				,objtrackerT_Person.DepartmentID AS C_DepartmentID
				,objtrackerT_Department.Title AS C_Department
				,objtrackerF_FormatDate(objtrackerT_Person.Track_Changed) AS C_Track_Changed
				,(SELECT COUNT(*) FROM objtrackerT_Objective WHERE OwnerID = objtrackerT_Person.ID) AS C_Usage
				,objtrackerF_FormatSortedDate(objtrackerT_Person.Track_Changed) AS C_Track_SortedChanged
				,objtrackerT_Person.Track_Userid AS C_Track_Userid
			FROM objtrackerT_Person  
			JOIN objtrackerT_Department ON objtrackerT_Person.DepartmentID = objtrackerT_Department.Id
							 AND objtrackerT_Person.OrganizationID = objtrackerT_Department.OrganizationID
			WHERE  objtrackerT_Person.OrganizationID = C_CallerOrg AND objtrackerT_Person.DepartmentID = C_Value
			;
		ELSEIF @Table = 'Objective' AND C_Column = 'DepartmentID' THEN
			SELECT 
			objtrackerT_Objective.ID AS C_ID
			,objtrackerT_Objective.Title AS C_Title
			,objtrackerT_MetricType.Title		AS C_MetricType
			,objtrackerT_Frequency.Title			AS C_Frequency
			,objtrackerT_Objective.Source			AS C_Source
			,fy1.Title					AS C_FiscalYear1
			,fy2.Title					AS C_FiscalYear2
			,objtrackerT_Person.FullName			AS C_Person
			,objtrackerT_Department.Title			AS C_Department
			,objtrackerT_ObjectiveType.Title		AS C_ObjectiveType
			,objtrackerF_FormatDate(objtrackerT_Objective.Track_Changed) AS C_Track_Changed
			,objtrackerF_FormatSortedDate(objtrackerT_Objective.Track_Changed) AS C_Track_SortedChanged
			,objtrackerT_Objective.Track_Userid AS C_Track_Userid
			FROM objtrackerT_Objective	  
			JOIN objtrackerT_MetricType			ON objtrackerT_Objective.MetricTypeID		= objtrackerT_MetricType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_MetricType.OrganizationID
			JOIN objtrackerT_Frequency			ON objtrackerT_Objective.FrequencyID		= objtrackerT_Frequency.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Frequency.OrganizationID
			JOIN objtrackerT_FiscalYear as fy1	ON objtrackerT_Objective.FiscalYear1		= fy1.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy1.OrganizationID
			JOIN objtrackerT_FiscalYear as fy2	ON objtrackerT_Objective.FiscalYear2		= fy2.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy2.OrganizationID
			JOIN objtrackerT_ObjectiveType		ON objtrackerT_Objective.TypeID			= objtrackerT_ObjectiveType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_ObjectiveType.OrganizationID
			JOIN objtrackerT_Person				ON objtrackerT_Objective.OwnerID			= objtrackerT_Person.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Person.OrganizationID
			JOIN objtrackerT_Department			ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
									   AND objtrackerT_Department.OrganizationID 	= objtrackerT_Person.OrganizationID
			WHERE  objtrackerT_Person.OrganizationID = C_CallerOrg AND objtrackerT_Person.DepartmentID = C_Value
			;
		ELSEIF @Table = 'Objective' AND C_Column = 'FiscalYear' THEN
			SELECT 
			objtrackerT_Objective.ID AS C_ID
			,objtrackerT_Objective.Title AS C_Title
			,objtrackerT_MetricType.Title		AS C_MetricType
			,objtrackerT_Frequency.Title			AS C_Frequency
			,objtrackerT_Objective.Source			AS C_Source
			,fy1.Title					AS C_FiscalYear1
			,fy2.Title					AS C_FiscalYear2
			,objtrackerT_Person.FullName			AS C_Person
			,objtrackerT_Department.Title			AS C_Department
			,objtrackerT_ObjectiveType.Title		AS C_ObjectiveType
			,objtrackerF_FormatDate(objtrackerT_Objective.Track_Changed) AS C_Track_Changed
			,objtrackerF_FormatSortedDate(objtrackerT_Objective.Track_Changed) AS C_Track_SortedChanged
			,objtrackerT_Objective.Track_Userid AS C_Track_Userid
			FROM objtrackerT_Objective	  
			JOIN objtrackerT_MetricType			ON objtrackerT_Objective.MetricTypeID		= objtrackerT_MetricType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_MetricType.OrganizationID
			JOIN objtrackerT_Frequency			ON objtrackerT_Objective.FrequencyID		= objtrackerT_Frequency.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Frequency.OrganizationID
			JOIN objtrackerT_FiscalYear as fy1	ON objtrackerT_Objective.FiscalYear1		= fy1.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy1.OrganizationID
			JOIN objtrackerT_FiscalYear as fy2	ON objtrackerT_Objective.FiscalYear2		= fy2.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy2.OrganizationID
			JOIN objtrackerT_ObjectiveType		ON objtrackerT_Objective.TypeID			= objtrackerT_ObjectiveType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_ObjectiveType.OrganizationID
			JOIN objtrackerT_Person				ON objtrackerT_Objective.OwnerID			= objtrackerT_Person.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Person.OrganizationID
			JOIN objtrackerT_Department			ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
									   AND objtrackerT_Department.OrganizationID 	= objtrackerT_Person.OrganizationID
			WHERE  objtrackerT_Objective.OrganizationID = C_CallerOrg AND objtrackerT_Objective.FiscalYear1 <= C_Value AND objtrackerT_Objective.FiscalYear2  >= C_Value
			;
		ELSEIF @Table = 'Objective' AND C_Column = 'FrequencyID' THEN
			SELECT 
			objtrackerT_Objective.ID AS C_ID
			,objtrackerT_Objective.Title AS C_Title
			,objtrackerT_MetricType.Title		AS C_MetricType
			,objtrackerT_Frequency.Title			AS C_Frequency
			,objtrackerT_Objective.Source			AS C_Source
			,fy1.Title					AS C_FiscalYear1
			,fy2.Title					AS C_FiscalYear2
			,objtrackerT_Person.FullName			AS C_Person
			,objtrackerT_Department.Title			AS C_Department
			,objtrackerT_ObjectiveType.Title		AS C_ObjectiveType
			,objtrackerF_FormatDate(objtrackerT_Objective.Track_Changed) AS C_Track_Changed
			,objtrackerF_FormatSortedDate(objtrackerT_Objective.Track_Changed) AS C_Track_SortedChanged
			,objtrackerT_Objective.Track_Userid AS C_Track_Userid
			FROM objtrackerT_Objective	  
			JOIN objtrackerT_MetricType			ON objtrackerT_Objective.MetricTypeID		= objtrackerT_MetricType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_MetricType.OrganizationID
			JOIN objtrackerT_Frequency			ON objtrackerT_Objective.FrequencyID		= objtrackerT_Frequency.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Frequency.OrganizationID
			JOIN objtrackerT_FiscalYear as fy1	ON objtrackerT_Objective.FiscalYear1		= fy1.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy1.OrganizationID
			JOIN objtrackerT_FiscalYear as fy2	ON objtrackerT_Objective.FiscalYear2		= fy2.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy2.OrganizationID
			JOIN objtrackerT_ObjectiveType		ON objtrackerT_Objective.TypeID			= objtrackerT_ObjectiveType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_ObjectiveType.OrganizationID
			JOIN objtrackerT_Person				ON objtrackerT_Objective.OwnerID			= objtrackerT_Person.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Person.OrganizationID
			JOIN objtrackerT_Department			ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
									   AND objtrackerT_Department.OrganizationID 	= objtrackerT_Person.OrganizationID
			WHERE  objtrackerT_Objective.OrganizationID = C_CallerOrg AND objtrackerT_Objective.FrequencyID = C_Value
			;
		ELSEIF @Table = 'Objective' AND C_Column = 'MetricTypeID' THEN
			SELECT 
				objtrackerT_Objective.ID AS C_ID
			,objtrackerT_Objective.Title AS C_Title
			,objtrackerT_MetricType.Title		AS C_MetricType
			,objtrackerT_Frequency.Title			AS C_Frequency
			,objtrackerT_Objective.Source			AS C_Source
			,fy1.Title					AS C_FiscalYear1
			,fy2.Title					AS C_FiscalYear2
			,objtrackerT_Person.FullName			AS C_Person
			,objtrackerT_Department.Title			AS C_Department
			,objtrackerT_ObjectiveType.Title		AS C_ObjectiveType
			,objtrackerF_FormatDate(objtrackerT_Objective.Track_Changed) AS C_Track_Changed
			,objtrackerF_FormatSortedDate(objtrackerT_Objective.Track_Changed) AS C_Track_SortedChanged
			,objtrackerT_Objective.Track_Userid AS C_Track_Userid
			FROM objtrackerT_Objective	  
			JOIN objtrackerT_MetricType			ON objtrackerT_Objective.MetricTypeID		= objtrackerT_MetricType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_MetricType.OrganizationID
			JOIN objtrackerT_Frequency			ON objtrackerT_Objective.FrequencyID		= objtrackerT_Frequency.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Frequency.OrganizationID
			JOIN objtrackerT_FiscalYear as fy1	ON objtrackerT_Objective.FiscalYear1		= fy1.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy1.OrganizationID
			JOIN objtrackerT_FiscalYear as fy2	ON objtrackerT_Objective.FiscalYear2		= fy2.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy2.OrganizationID
			JOIN objtrackerT_ObjectiveType		ON objtrackerT_Objective.TypeID			= objtrackerT_ObjectiveType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_ObjectiveType.OrganizationID
			JOIN objtrackerT_Person				ON objtrackerT_Objective.OwnerID			= objtrackerT_Person.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Person.OrganizationID
			JOIN objtrackerT_Department			ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
									   AND objtrackerT_Department.OrganizationID 	= objtrackerT_Person.OrganizationID
			WHERE  objtrackerT_Objective.OrganizationID = C_CallerOrg AND objtrackerT_Objective.MetricTypeID = C_Value
			;
		ELSEIF @Table = 'Objective' AND C_Column = 'TypeID' THEN	-- ObjectiveTypeID
			SELECT 
			objtrackerT_Objective.ID AS C_ID
			,objtrackerT_Objective.Title AS C_Title
			,objtrackerT_MetricType.Title		AS C_MetricType
			,objtrackerT_Frequency.Title			AS C_Frequency
			,objtrackerT_Objective.Source			AS C_Source
			,fy1.Title					AS C_FiscalYear1
			,fy2.Title					AS C_FiscalYear2
			,objtrackerT_Person.FullName			AS C_Person
			,objtrackerT_Department.Title			AS C_Department
			,objtrackerT_ObjectiveType.Title		AS C_ObjectiveType
			,objtrackerF_FormatDate(objtrackerT_Objective.Track_Changed) AS C_Track_Changed
			,objtrackerF_FormatSortedDate(objtrackerT_Objective.Track_Changed) AS C_Track_SortedChanged
			,objtrackerT_Objective.Track_Userid AS C_Track_Userid
			FROM objtrackerT_Objective	  
			JOIN objtrackerT_MetricType			ON objtrackerT_Objective.MetricTypeID		= objtrackerT_MetricType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_MetricType.OrganizationID
			JOIN objtrackerT_Frequency			ON objtrackerT_Objective.FrequencyID		= objtrackerT_Frequency.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Frequency.OrganizationID
			JOIN objtrackerT_FiscalYear as fy1	ON objtrackerT_Objective.FiscalYear1		= fy1.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy1.OrganizationID
			JOIN objtrackerT_FiscalYear as fy2	ON objtrackerT_Objective.FiscalYear2		= fy2.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy2.OrganizationID
			JOIN objtrackerT_ObjectiveType		ON objtrackerT_Objective.TypeID			= objtrackerT_ObjectiveType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_ObjectiveType.OrganizationID
			JOIN objtrackerT_Person				ON objtrackerT_Objective.OwnerID			= objtrackerT_Person.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Person.OrganizationID
			JOIN objtrackerT_Department			ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
									   AND objtrackerT_Department.OrganizationID 	= objtrackerT_Person.OrganizationID
			WHERE  objtrackerT_Objective.OrganizationID = C_CallerOrg AND objtrackerT_Objective.TypeID = C_Value
			;
		ELSEIF @Table = 'Objective' AND C_Column = 'OwnerID' THEN
			SELECT 
				objtrackerT_Objective.ID AS C_ID
			,objtrackerT_Objective.Title AS C_Title
			,objtrackerT_MetricType.Title		AS C_MetricType
			,objtrackerT_Frequency.Title			AS C_Frequency
			,objtrackerT_Objective.Source			AS C_Source
			,fy1.Title					AS C_FiscalYear1
			,fy2.Title					AS C_FiscalYear2
			,objtrackerT_Person.FullName			AS C_Person
			,objtrackerT_Department.Title			AS C_Department
			,objtrackerT_ObjectiveType.Title		AS C_ObjectiveType
			,objtrackerF_FormatDate(objtrackerT_Objective.Track_Changed) AS C_Track_Changed
			,objtrackerF_FormatSortedDate(objtrackerT_Objective.Track_Changed) AS C_Track_SortedChanged
			,objtrackerT_Objective.Track_Userid AS C_Track_Userid
			FROM objtrackerT_Objective	  
			JOIN objtrackerT_MetricType			ON objtrackerT_Objective.MetricTypeID		= objtrackerT_MetricType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_MetricType.OrganizationID
			JOIN objtrackerT_Frequency			ON objtrackerT_Objective.FrequencyID		= objtrackerT_Frequency.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Frequency.OrganizationID
			JOIN objtrackerT_FiscalYear as fy1	ON objtrackerT_Objective.FiscalYear1		= fy1.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy1.OrganizationID
			JOIN objtrackerT_FiscalYear as fy2	ON objtrackerT_Objective.FiscalYear2		= fy2.Id
									   AND objtrackerT_Objective.OrganizationID 	= fy2.OrganizationID
			JOIN objtrackerT_ObjectiveType		ON objtrackerT_Objective.TypeID			= objtrackerT_ObjectiveType.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_ObjectiveType.OrganizationID
			JOIN objtrackerT_Person				ON objtrackerT_Objective.OwnerID			= objtrackerT_Person.Id
									   AND objtrackerT_Objective.OrganizationID 	= objtrackerT_Person.OrganizationID
			JOIN objtrackerT_Department			ON objtrackerT_Department.ID				= objtrackerT_Person.DepartmentID
									   AND objtrackerT_Department.OrganizationID 	= objtrackerT_Person.OrganizationID
			WHERE  objtrackerT_Objective.OrganizationID = C_CallerOrg AND objtrackerT_Objective.OwnerID = C_Value
			;
		ELSE
			SELECT @Table ; -- 'Invalid parms for objtrackerP_Usage.sql';
		END IF;
	END IF;
END
$$
