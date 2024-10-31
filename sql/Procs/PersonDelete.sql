DROP PROCEDURE IF EXISTS objtrackerP_PersonDelete;
DELIMITER $$
/*
	CALL objtrackerP_PersonDelete (3)
*/
CREATE PROCEDURE objtrackerP_PersonDelete (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	,C_ID					INT  
)
BEGIN
	DELETE FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_ID;
	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
END
$$
