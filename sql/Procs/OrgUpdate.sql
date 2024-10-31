DROP PROCEDURE IF EXISTS objtrackerP_OrgUpdate;
DELIMITER $$

/*
	Call objtrackerP_OrgUpdate (2, 'name2','path2','me')
	call objtrackerP_OrgList()
*/
CREATE PROCEDURE objtrackerP_OrgUpdate (
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_Title			VARCHAR (32)
	,C_UploadFsPath 	VARCHAR(150)	
)
BEGIN
	IF (C_Title IS NULL OR C_Title = '') THEN
		SELECT 'OrgUpdTitle' AS C_ErrorID, 'Title is required' AS C_ErrorMessage;
	ELSEIF (C_UploadFsPath IS NULL OR C_UploadFsPath = '') THEN
		SELECT 'OrgUpdPath' AS C_ErrorID, 'Upload path is required' AS C_ErrorMessage;
	ELSE	
		UPDATE objtrackerT_Organization SET
	  		Title = C_Title
  			,UploadFsPath = C_UploadFsPath
  			,Track_Changed = Now()
  			,Track_Userid = 'su-admin'
  		WHERE ID = C_CallerOrg;
	
		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
