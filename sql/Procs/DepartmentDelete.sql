DROP PROCEDURE IF EXISTS objtrackerP_DepartmentDelete;
DELIMITER $$
/*
	CALL objtrackerP_DepartmentDelete (3)
*/
CREATE PROCEDURE objtrackerP_DepartmentDelete(
	C_CallerOrg		INT
	,C_CallerUser	INT
	,C_ID			INT
)
BEGIN
	IF EXISTS (SELECT * FROM objtrackerT_Department WHERE ID <> C_ID) THEN
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;
		UPDATE objtrackerT_Department SET Track_UserID = @UserName WHERE OrganizationID = C_CallerOrg AND ID = C_ID;

		DELETE FROM objtrackerT_Department WHERE OrganizationID = C_CallerOrg AND ID = C_ID;

		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	ELSE
		SELECT 'DeptDel1' AS C_ErrorID, 'Cannot delete last department' AS C_ErrorMessage;
	END IF;
END
$$
