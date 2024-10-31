DROP PROCEDURE IF EXISTS objtrackerP_MeasurementInsert;
DELIMITER $$
/*
	CALL objtrackerP_MeasurementInsert( '1','2012-10-01','2012-01-01','note','me');
 */
CREATE PROCEDURE objtrackerP_MeasurementInsert(
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_ObjectiveID		VARCHAR(8) 
	,C_PeriodStarting	VARCHAR(32)
	,C_Measurement		VARCHAR(32)
	,C_Notes 			VARCHAR(4096)
)
BEGIN
	IF C_Notes IS NULL THEN
		SET C_Notes := '';
	END IF;

	SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

	IF EXISTS (SELECT * FROM objtrackerT_Measurement WHERE OrganizationID = C_CallerOrg AND ObjectiveID = C_ObjectiveID AND PeriodStarting = C_PeriodStarting) THEN
		UPDATE objtrackerT_Measurement SET
			Measurement = C_Measurement
			,Notes = C_Notes
			,Track_Userid = @UserName
		WHERE OrganizationID = C_CallerOrg AND ObjectiveID = C_ObjectiveID AND PeriodStarting = C_PeriodStarting;
	ELSE
		SELECT MAX(ID)+1 INTO @ID FROM objtrackerT_Measurement WHERE OrganizationID = C_CallerOrg;
		IF @ID IS NULL THEN
			set @ID := 1;
		END IF;
		INSERT INTO objtrackerT_Measurement (
			ID
			,OrganizationID
			,ObjectiveID
			,PeriodStarting
			,Measurement
			,Notes
			,Track_Changed
			,Track_Userid
		) VALUES(
			@ID
			,C_CallerOrg
			,C_ObjectiveID
			,C_PeriodStarting
			,C_Measurement
			,C_Notes
			,NOW()
			,@UserName
		);
	END IF;

	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
END
$$
