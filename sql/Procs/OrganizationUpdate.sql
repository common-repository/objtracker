DROP PROCEDURE IF EXISTS objtrackerP_OrganizationUpdate;
DELIMITER $$

/*
	EXEC objtrackerP_OrganizationUpdate 1, 'ab','cd',1
*/
CREATE PROCEDURE objtrackerP_OrganizationUpdate (
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_Title			VARCHAR (32)
	,C_ShortTitle		VARCHAR (32)
	,C_FirstMonth		INT
	,C_IsChangePassword	VARCHAR (3)
	,C_UploadFsPath 	VARCHAR(150)	
	,C_Trailer			VARCHAR(48)
)
BEGIN
	DECLARE C_bChangePassword BIT;
	IF C_IsChangePassword = 'Yes' THEN
		SET C_bChangePassword := 1;
	ELSE
		SET C_bChangePassword := 0;
	END IF;
	IF C_Trailer IS NULL THEN
		SET C_Trailer := '';
	END IF;

	IF (C_Title IS NULL OR C_Title = '') THEN
		SELECT 'OrgUpdTitle' AS C_ErrorID, 'Title is required' AS C_ErrorMessage;
	ELSEIF (C_ShortTitle IS NULL OR C_ShortTitle = '') THEN
		SELECT 'OrgUpdTitle2' AS C_ErrorID, 'Short Title is required' AS C_ErrorMessage;
	ELSEIF (C_UploadFsPath IS NULL OR C_UploadFsPath = '') THEN
		SELECT 'OrgUpdateDup' AS C_ErrorID, 'Upload path is required' AS C_ErrorMessage;
	ELSE	
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

		UPDATE objtrackerT_Organization SET
	  		Title = C_Title
  			,ShortTitle = C_ShortTitle
  			,FirstMonth = C_FirstMonth
  			,UploadFsPath = C_UploadFsPath
  			,Trailer = C_Trailer
  			,ChangePassword = C_bChangePassword
  			,Track_Changed = Now()
  			,Track_Userid = @UserName
  		WHERE ID = C_CallerOrg;

		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
