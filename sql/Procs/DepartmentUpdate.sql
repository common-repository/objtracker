DROP PROCEDURE IF EXISTS objtrackerP_DepartmentUpdate;
DELIMITER $$
/*
	CALL objtrackerP_DepartmentUpdate (1,1, 'Yes', 1, 'ab','cd',1()
*/
CREATE PROCEDURE objtrackerP_DepartmentUpdate(
	C_CallerOrg		INT
	,C_CallerUser	INT
	,C_ID			INT
	,C_IsActive		VARCHAR (3)
	,C_Title		VARCHAR (64)
	,C_Title2		VARCHAR (16)
)
BEGIN
	DECLARE bActive BIT;
	IF C_IsActive = 'Yes' THEN
		SET bActive := 1;
	ELSE
		SET bActive := 0;
	END IF;
	
 	IF LENGTH(C_Title) = 0 THEN
		SELECT 'DeptUpdTitle' AS C_ErrorID, 'Title not specified' AS C_ErrorMessage;
	ELSEIF LENGTH(C_Title2) = 0 THEN
		SELECT 'DeptUpdTitle2' AS C_ErrorID, 'Short title not specified' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_Department WHERE OrganizationID = C_CallerOrg AND Title = C_Title AND ID != C_ID) THEN
		SELECT 'DeptUpdDupTitle' AS C_ErrorID, 'Title already exists' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_Department WHERE OrganizationID = C_CallerOrg AND Title2 = C_Title2 AND ID != C_ID) THEN
		SELECT 'DeptUpdDupTitle2' AS C_ErrorID, 'Short title already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

		UPDATE objtrackerT_Department SET
			Active = bActive
			,Title = C_Title
			,Title2 = C_Title2
			,Track_Changed = Current_Timestamp
			,Track_Userid = @UserName
		WHERE OrganizationID = C_CallerOrg AND ID = C_ID;

		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
  END IF;
END
$$
