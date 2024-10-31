DROP PROCEDURE IF EXISTS objtrackerP_OrgInsert;
DELIMITER $$
/*
	call objtrackerP_OrgInsert (1,1,'title', 'path','me');
	call objtrackerP_OrgDelete(1,1,2);
 	SELECT * from objtrackerT_Organization
*/
CREATE PROCEDURE objtrackerP_OrgInsert(
	C_CallerOrg		INT
	,C_CallerUser	INT
	,C_Title		VARCHAR (64)
	,C_UploadFsPath	VARCHAR (150)
)
BEGIN
	IF (C_Title IS NULL OR C_Title = '') THEN
		SELECT 'OrgInsTitle' AS C_ErrorID, 'Title field is required' AS C_ErrorMessage;
	ELSEIF (C_UploadFsPath IS NULL OR C_UploadFsPath = '') THEN
		SELECT 'OrgInsPath' AS C_ErrorID, 'Upload path is required' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_Organization WHERE Title = C_Title) THEN
		SELECT 'OrgInsPath' AS C_ErrorID, 'Title already exists' AS C_ErrorMessage;
	ELSE
		CALL objtrackerP_OrgNInit(C_CallerOrg);

		UPDATE objtrackerT_Organization SET
	  		Title = C_Title
  			,UploadFsPath = C_UploadFsPath
  			,Track_Changed = Now()
  			,Track_Userid = @UserName
  		WHERE ID = C_CallerOrg;

		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
