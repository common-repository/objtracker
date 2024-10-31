DROP PROCEDURE IF EXISTS objtrackerP_DocumentationDelete;
DELIMITER $$
/*
	call objtrackerP_DocumentationDelete( 1) 
*/
CREATE PROCEDURE objtrackerP_DocumentationDelete (
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_ID			INT  
)
BEGIN
	DELETE FROM objtrackerT_Documentation WHERE OrganizationID = C_CallerOrg AND ID = C_ID ;
	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
END
$$
