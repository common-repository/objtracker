DROP PROCEDURE IF EXISTS objtrackerP_UsageTitle;
DELIMITER $$
/*
	call objtrackerP_UsageTitle (1,1,'objtrackerT_Person','DepartmentID','1')
	call objtrackerP_UsageTitle (1,1,'objtrackerT_Objective','FrequencyID','Q')
	call objtrackerP_UsageTitle (1,1,'objtrackerT_Objective','MetricTypeID','I')
	call objtrackerP_UsageTitle (1,1,'objtrackerT_Objective','TypeID','C')
	call objtrackerP_UsageTitle (1,1,'objtrackerT_Objective','OwnerID','1')
*/
CREATE PROCEDURE objtrackerP_UsageTitle (
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
				C_Table AS C_TableName
				,C_Column AS C_Column
				,C_Value As C_Value
				,Title AS C_Representing
			FROM objtrackerT_Department  
			WHERE OrganizationID = C_CallerOrg AND ID = C_Value;
		ELSEIF @Table = 'Objective' AND C_Column = 'FrequencyID' THEN
			SELECT
				C_Table AS C_TableName
				,C_Column AS C_Column
				,C_Value As C_Value
				,Title AS C_Representing
			FROM objtrackerT_Frequency  
			WHERE OrganizationID = C_CallerOrg AND ID = C_Value;
		ELSEIF @Table = 'Objective' AND C_Column = 'FiscalYear' THEN
			SELECT
				C_Table AS C_TableName
				,C_Column AS C_Column
				,C_Value As C_Value
				,Title AS C_Representing
			FROM objtrackerT_FiscalYear  
			WHERE OrganizationID = C_CallerOrg AND ID = C_Value;
		ELSEIF @Table = 'Objective' AND C_Column = 'MetricTypeID' THEN
			SELECT
				C_Table AS C_TableName
				,C_Column AS C_Column
				,C_Value As C_Value
				,Title AS C_Representing
			FROM objtrackerT_MetricType  
			WHERE OrganizationID = C_CallerOrg AND ID = C_Value;
		ELSEIF @Table = 'Objective' AND C_Column = 'TypeID' THEN	-- ObjectiveTypeID
			SELECT
				C_Table AS C_TableName
				,C_Column AS C_Column
				,C_Value As C_Value
				,Title AS C_Representing
			FROM objtrackerT_ObjectiveType  
			WHERE OrganizationID = C_CallerOrg AND ID = C_Value;
		ELSEIF @Table = 'Objective' AND C_Column = 'OwnerID' THEN
			SELECT
				C_Table AS C_TableName
				,C_Column AS C_Column
				,C_Value As C_Value
				,FullName AS C_Representing
			FROM objtrackerT_Person  
			WHERE OrganizationID = C_CallerOrg AND ID = C_Value;
		ELSE
			SELECT 'Invalid parms for objtrackerP_UsageTitle.sql';
		END IF;
	END IF;
END
$$
