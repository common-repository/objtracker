DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveTypeUpdate;
DELIMITER $$
/*
	EXEC objtrackerP_ObjectiveTypeUpdate 1, 'ab','cd',1
*/
CREATE PROCEDURE objtrackerP_ObjectiveTypeUpdate(
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_ID				CHAR
	,C_Title			VARCHAR (64)
	,C_Description		VARCHAR (128)
)
BEGIN
	IF (C_Title IS NULL OR C_Title = '') THEN
		SELECT 'TypeUpdTitle' AS C_ErrorID, 'Title is required' AS C_ErrorMessage;
	ELSEIF (C_Description IS NULL OR C_Description = '') THEN
		SELECT 'TypeUpdDesc' AS C_ErrorID, 'Description is required' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_ObjectiveType WHERE OrganizationID = C_CallerOrg AND Title = C_Title AND ID != C_ID) THEN
		SELECT 'TypeUpdDupTitle' AS C_ErrorID, 'Title already exists' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_ObjectiveType WHERE OrganizationID = C_CallerOrg AND Title2 = C_Description AND ID != C_ID) THEN
		SELECT 'TypeUpdDupDesc' AS C_ErrorID, 'Description already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

		UPDATE objtrackerT_ObjectiveType SET
			Title = C_Title
			,Description = C_Description
			,Track_Changed = Current_Timestamp
			,Track_Userid = @UserName
		WHERE OrganizationID = C_CallerOrg AND ID = C_ID;

		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
