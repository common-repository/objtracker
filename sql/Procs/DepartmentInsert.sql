DROP PROCEDURE IF EXISTS objtrackerP_DepartmentInsert;
DELIMITER $$
/*
	call objtrackerP_DepartmentInsert (1,1,'title', 'title2');
	SELECT * from objtrackerT_Department
*/
CREATE PROCEDURE objtrackerP_DepartmentInsert(
	C_CallerOrg		INT
	,C_CallerUser	INT
	,C_Title		VARCHAR (32)
	,C_Title2		VARCHAR (16)
)
BEGIN
	 IF (C_Title IS NULL OR C_Title = '') THEN
		 SELECT 'DeptInsTitle' AS C_ErrorID, 'Title field is required' AS C_ErrorMessage;
	 ELSEIF (C_Title2 IS NULL OR C_Title2 = '') THEN
		 SELECT 'DeptInsTitle2' AS C_ErrorID, 'Short title field is required' AS C_ErrorMessage;
	 ELSEIF EXISTS (SELECT * FROM objtrackerT_Department WHERE  OrganizationID = C_CallerOrg AND Title = C_Title) THEN
		 SELECT 'DeptInsDupTitle' AS C_ErrorID, 'Title already exists' AS C_ErrorMessage;
	 ELSEIF EXISTS (SELECT * FROM objtrackerT_Department WHERE  OrganizationID = C_CallerOrg AND Title2 = C_Title2) THEN
		 SELECT 'DeptInsDupTitle2' AS C_ErrorID, 'Short title already exists' AS C_ErrorMessage;
	 ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

		INSERT INTO objtrackerT_Department (
			ID
			,OrganizationID
			,ParentID
			,Title, Title2
			,Active
			,Track_Changed
			,Track_Userid
		)
		SELECT
			IFNULL( (SELECT MAX(ID)+1 from objtrackerT_Department WHERE OrganizationID = C_CallerOrg), 1)
			,C_CallerOrg
			,0
			,C_Title,C_Title2
			,1
			,Now()
			,@UserName;

		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
