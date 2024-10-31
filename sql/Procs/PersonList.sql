DROP PROCEDURE IF EXISTS objtrackerP_PersonList;
DELIMITER $$
/*
	Call objtrackerP_PersonList (1,1,'False'); --no inactive
	Call objtrackerP_PersonList (1,1,'True'); -- wants inactive
*/
CREATE PROCEDURE objtrackerP_PersonList (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	,C_Inactive 			VARCHAR(8)  
)
BEGIN
	IF C_Inactive = 'True' THEN
		SELECT 
			objtrackerT_Person.ID AS C_ID
			,objtrackerT_Person.Active AS C_Active
			,Case WHEN objtrackerT_Person.Admin = 1 THEN 'Yes' ELSE 'No' END AS C_IsAdmin
			,Case WHEN objtrackerT_Person.Active = 1 THEN 'Yes' ELSE 'No' END AS C_IsActive
			,objtrackerT_Person.FullName AS C_FullName
	  ,Case WHEN objtrackerT_Person.Active = 1 THEN objtrackerT_Person.FullName ELSE CONCAT('Inactive-',objtrackerT_Person.FullName) END AS C_InactiveName
			,objtrackerT_Person.UserName AS C_UserName
			,objtrackerT_Person.DepartmentID AS C_DepartmentID
			,objtrackerT_Department.Title AS C_Department
			,objtrackerF_FormatDate(objtrackerT_Person.Track_Changed) AS C_Track_Changed
			, (SELECT COUNT(*) FROM objtrackerT_Objective WHERE OwnerID = objtrackerT_Person.ID) AS C_Usage
			,objtrackerF_FormatSortedDate(objtrackerT_Person.Track_Changed) AS C_Track_SortedChanged
			,objtrackerT_Person.Track_Userid AS C_Track_Userid
		FROM	objtrackerT_Person  
		JOIN objtrackerT_Department ON objtrackerT_Person.DepartmentID = objtrackerT_Department.Id
		WHERE objtrackerT_Person.OrganizationID = C_CallerOrg
		ORDER BY objtrackerT_Person.UserName;
	ELSE
		SELECT 
			objtrackerT_Person.ID AS C_ID
			,objtrackerT_Person.Active AS C_Active
			,Case WHEN objtrackerT_Person.Admin = 1 THEN 'Yes' ELSE 'No' END AS C_IsAdmin
			,Case WHEN objtrackerT_Person.Active = 1 THEN 'Yes' ELSE 'No' END AS C_IsActive
			,objtrackerT_Person.FullName AS C_FullName
	  ,Case WHEN objtrackerT_Person.Active = 1 THEN objtrackerT_Person.FullName ELSE CONCAT('Inactive-',objtrackerT_Person.FullName) END AS C_InactiveName
			,objtrackerT_Person.UserName AS C_UserName
			,objtrackerT_Person.DepartmentID AS C_DepartmentID
			,objtrackerT_Department.Title  AS C_Department
			, (SELECT COUNT(*) FROM objtrackerT_Objective WHERE OwnerID = objtrackerT_Person.ID) AS C_Usage
			,objtrackerF_FormatDate(objtrackerT_Person.Track_Changed) AS C_Track_Changed
			,objtrackerF_FormatSortedDate(objtrackerT_Person.Track_Changed) AS C_Track_SortedChanged
			,objtrackerT_Person.Track_Userid  AS C_Track_Userid
		FROM
			objtrackerT_Person  
		JOIN objtrackerT_Department ON objtrackerT_Person.DepartmentID = objtrackerT_Department.Id
		WHERE objtrackerT_Person.OrganizationID = C_CallerOrg
		  AND objtrackerT_Person.Active = 1
		ORDER BY objtrackerT_Person.UserName;
	END IF;
END
$$
