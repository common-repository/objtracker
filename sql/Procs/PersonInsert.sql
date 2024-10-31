DROP PROCEDURE IF EXISTS objtrackerP_PersonInsert;
DELIMITER $$

/*
	CALL objtrackerP_PersonInsert ('1', 'No','Full7','UName7','1','me')
*/
CREATE PROCEDURE objtrackerP_PersonInsert (
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_Admin			VARCHAR (3)
	,C_FullName			VARCHAR (32)
	,C_UserName			VARCHAR (32)
	,C_DepartmentID		VARCHAR (3)
)
BEGIN
	IF C_Admin = 'Yes' THEN
		SET @IsAdmin := 1;
	ELSE
		SET @IsAdmin := 0;
	END IF;
	IF LENGTH(C_FullName) = 0 THEN
		SELECT 'PerInsName' AS C_ErrorID, 'Full name not specified' AS C_ErrorMessage;
	ELSEIF LENGTH(C_UserName) = 0 THEN
		SELECT 'PerInsLogon' AS C_ErrorID, 'Logon ID not specified' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND UserName = C_UserName) THEN
		SELECT 'PerInsDup' AS C_ErrorID, 'Login ID already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;
		IF @UserName is NULL THEN
			SET @UserName := C_UserName;
		END IF;
		SELECT DefaultPassword INTO @Password FROM objtrackerT_Organization WHERE ID = C_CallerOrg;
 
		INSERT INTO objtrackerT_Person (
			ID
			,OrganizationID
			,Admin
			,Active
			,FullName
			,UserName
			,Password
			,DepartmentID
			,UiSettings
			,Track_Changed
			,Track_Userid
		)
		SELECT
			IFNULL( (SELECT MAX(ID)+1 from objtrackerT_Person), 1)
			,C_CallerOrg
			,@IsAdmin
			,1
			,C_FullName
			,C_UserName
			,@Password
			,C_DepartmentID
			,'SSSS............'
			,now()
			,@UserName;

		IF @UserName <> 'initialAdmin' THEN
			SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
		END IF;
	END IF;

END
$$
