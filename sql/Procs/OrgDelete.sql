DROP PROCEDURE IF EXISTS objtrackerP_OrgDelete;
DELIMITER $$
/*
	CALL objtrackerP_OrgDelete (2)
*/
CREATE PROCEDURE objtrackerP_OrgDelete(
	C_CallerOrg				INT 
	,C_CallerUser			INT 
)
BEGIN
	DELETE FROM objtrackerT_Organization WHERE ID = C_CallerOrg;
	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
END
$$
