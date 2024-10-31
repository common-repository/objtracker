DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveDelete;
DELIMITER $$
/*
	EXEC objtrackerP_ObjectiveDelete 1, 'ab','cd',1
*/
CREATE PROCEDURE objtrackerP_ObjectiveDelete (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	,C_ID			INT   
)
BEGIN
	IF EXISTS (SELECT * FROM objtrackerT_Measurement WHERE OrganizationID = C_CallerOrg AND ObjectiveID = C_ID) THEN
		SELECT 'ObjDelHas' AS C_ErrorID, 'Objective has measurements, can not delete!' AS C_ErrorMessage;
	ELSE 
		DELETE FROM objtrackerT_Target 	WHERE OrganizationID = C_CallerOrg AND ID = C_ID;
		DELETE FROM objtrackerT_Objective WHERE OrganizationID = C_CallerOrg AND ID = C_ID;
		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
