DROP PROCEDURE IF EXISTS objtrackerP_MetricTypeUpdate;
DELIMITER $$

/*
	EXEC objtrackerP_MetricTypeUpdate 1, 'ab','cd',1
*/
CREATE PROCEDURE objtrackerP_MetricTypeUpdate(
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_ID				CHAR
	,C_Title			VARCHAR (32)
	,C_Description		VARCHAR (32)
)
BEGIN
	IF (C_Title IS NULL OR C_Title = '') THEN
		SELECT 'MetUpdTitle' AS C_ErrorID, 'Title is required' AS C_ErrorMessage;
	ELSEIF (C_Description IS NULL OR C_Description = '') THEN
		SELECT 'MetUpdDesc' AS C_ErrorID, 'Description is required' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_MetricType WHERE OrganizationID = C_CallerOrg AND Title = C_Title AND ID != C_ID) THEN
		SELECT 'MetUpdDup' AS C_ErrorID, 'Title already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

		UPDATE objtrackerT_MetricType SET
			Title = C_Title
			,Description = C_Description
			,Track_Changed = Current_Timestamp
			,Track_Userid = @UserName
		WHERE OrganizationID = C_CallerOrg AND ID = C_ID;
	END IF;

	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
END
$$
