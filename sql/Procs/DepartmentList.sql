DROP PROCEDURE IF EXISTS objtrackerP_DepartmentList;
DELIMITER $$
/*
	CALL objtrackerP_DepartmentList(1,1,'False','False','NotUsed')
*/
CREATE PROCEDURE objtrackerP_DepartmentList(
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_Active			VARCHAR(8)
	,C_All		 		VARCHAR(8)
	,C_Userid	 		VARCHAR(8)
)
BEGIN
	IF C_All = 'True' THEN
		SELECT '0-99999' AS C_ID ,'All' AS C_Title, 'Yes' AS C_IsActive	
		UNION
		SELECT CONCAT('U',C_Userid) AS C_ID ,'All assigned to me' AS C_Title, 'Yes' AS C_IsActive
		UNION
		SELECT
			CAST(ID AS CHAR(8))
			,Concat('Dept: ' , Title) AS C_Title
			,CASE WHEN Active = 1 THEN 'Yes' ELSE 'No' END AS C_IsActive
		FROM objtrackerT_Department
		WHERE OrganizationID = C_CallerOrg
		ORDER BY 3 DESC, 2;
	ELSE
		DROP TABLE IF EXISTS C_UsageTable;
		CREATE TEMPORARY TABLE C_UsageTable (DID int, C_Usage int);

		INSERT INTO C_UsageTable (DID, C_Usage) (
			SELECT objtrackerT_Person.DepartmentID , COUNT(*)
			FROM objtrackerT_Person JOIN objtrackerT_Department  ON objtrackerT_Person.DepartmentID 	= objtrackerT_Department.ID
											AND objtrackerT_Person.OrganizationID = objtrackerT_Department.OrganizationID
			WHERE objtrackerT_Person.OrganizationID = C_CallerOrg
			GROUP BY objtrackerT_Person.DepartmentID
		);
		IF C_Active = 'True' THEN
			SELECT
				objtrackerT_Department.ID AS C_ID
				,objtrackerT_Department.Title AS C_Title
	 			,objtrackerT_Department.Title2 AS C_Title2
				,objtrackerT_Department.ParentID AS C_ParentID
				,objtrackerT_Department.Active AS C_Active
				,Case WHEN objtrackerT_Department.Active = 1 THEN 'Yes' ELSE 'No' END AS C_IsActive
				,CASE WHEN C_Usage IS NULL THEN 0 ELSE C_Usage END AS C_Usage
				,objtrackerF_FormatDate(objtrackerT_Department.Track_Changed) AS C_Track_Changed
				,objtrackerF_FormatSortedDate(objtrackerT_Department.Track_Changed) AS C_Track_SortedChanged
				,objtrackerT_Department.Track_Userid as C_Track_Userid
			FROM objtrackerT_Department
			JOIN C_UsageTable ON DID = objtrackerT_Department.ID
			WHERE objtrackerT_Department.Active = 1
			  AND objtrackerT_Department.OrganizationID = C_CallerOrg
			ORDER BY objtrackerT_Department.Title ;
		ELSE
			SELECT
				objtrackerT_Department.ID AS C_ID,'xxx'
				,objtrackerT_Department.Title AS C_Title
	 			,objtrackerT_Department.Title2 AS C_Title2
				,objtrackerT_Department.ParentID AS C_ParentID
				,objtrackerT_Department.Active AS C_Active
				,Case WHEN objtrackerT_Department.Active = 1 THEN 'Yes' ELSE 'No' END AS C_IsActive
				,CASE WHEN C_Usage IS NULL THEN 0 ELSE C_Usage END AS C_Usage
				,objtrackerF_FormatDate(objtrackerT_Department.Track_Changed) AS C_Track_Changed
				,objtrackerF_FormatSortedDate(objtrackerT_Department.Track_Changed) AS C_Track_SortedChanged
				,objtrackerT_Department.Track_Userid as C_Track_Userid
			FROM objtrackerT_Department
			LEFT JOIN C_UsageTable 	ON DID = objtrackerT_Department.ID
			WHERE objtrackerT_Department.OrganizationID = C_CallerOrg
			ORDER BY objtrackerT_Department.Active DESC, objtrackerT_Department.Title;
 		END IF;
	END IF;
END
$$
