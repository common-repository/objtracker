DROP PROCEDURE IF EXISTS objtrackerP_MeasurementDelete;
DELIMITER $$
/*
	SELECT * FROM objtrackerT_Measurement
	DELETE FROM objtrackerT_Measurement where ID = 1
	EXEC objtrackerP_MeasurementDelete 1, 'ab' 
*/
CREATE PROCEDURE objtrackerP_MeasurementDelete (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	,C_ID					INT  
)
BEGIN	
	SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;
	SELECT PeriodStarting, ObjectiveID INTO @PeriodStarting, @ObjectiveID 
		FROM objtrackerT_Measurement WHERE OrganizationID = C_CallerOrg AND ID = C_ID;

	IF @PeriodStarting IS NULL THEN
		SELECT 'MeaDelBug' AS C_ErrorID, 'Error getting PeriodStarting' AS C_ErrorMessage;
	ELSE 
		IF EXISTS (SELECT * FROM objtrackerT_Documentation WHERE  OrganizationID = C_CallerOrg AND ObjectiveID = @ObjectiveID AND PeriodStarting = @PeriodStarting) THEN
			SELECT 'MeaDelAtt' AS C_ErrorID, 'Objective has attachments, can not delete' AS C_ErrorMessage;
		ELSE 
			UPDATE objtrackerT_Measurement SET Track_UserID = C_CallerUser WHERE  OrganizationID = C_CallerOrg AND ID = C_ID;
			DELETE FROM objtrackerT_Measurement  WHERE  OrganizationID = C_CallerOrg AND ID = C_ID;
			SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
		END IF;
	END IF;
END
$$
