DROP PROCEDURE IF EXISTS objtrackerP_PersonUpdate;
DELIMITER $$

CREATE PROCEDURE objtrackerP_PersonUpdate (
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_ID				INT
	,C_DepartmentID	  	INT
	,C_IsAdmin			VARCHAR (3)
	,C_IsActive			VARCHAR (3)
	,C_FullName			VARCHAR (32)
	,C_UserName			VARCHAR (32)
)
BEGIN
	DECLARE C_bAdmin BIT;
	DECLARE C_bActive BIT;
	IF C_IsAdmin = 'Yes' THEN
		SET C_bAdmin := 1;
	ELSE
		SET C_bAdmin := 0;
  END IF;

	IF C_IsActive = 'Yes' THEN
		SET C_bActive := 1;
	ELSE
		SET C_bActive := 0;
  END IF;
	IF LENGTH(C_FullName) = 0 THEN
		SELECT 'PerUpdName' AS C_ErrorID, 'Full name not specified' AS C_ErrorMessage;
	ELSEIF LENGTH(C_UserName) = 0 THEN
		SELECT 'PerUpdLogon' AS C_ErrorID, 'Logon ID not specified' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND UserName = C_UserName AND ID != C_ID) THEN
		SELECT 'PerUpdDup' AS C_ErrorID, 'Login ID already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

	  	UPDATE objtrackerT_Person SET
  			Admin = C_bAdmin
			,Active = C_bActive
			,FullName = C_FullName
			,UserName = C_UserName
			,DepartmentID =C_DepartmentID
			,Track_Changed = now()
			,Track_Userid = @UserName
		WHERE OrganizationID = C_CallerOrg AND ID = C_ID;
	
	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
  END IF;
END
$$
