DROP PROCEDURE IF EXISTS objtrackerP_MeasurementUpdate;
DELIMITER $$
CREATE PROCEDURE objtrackerP_MeasurementUpdate(
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_ID				INT
	,C_Docs1Key			VARCHAR (32)  -- ignored
	,C_Measurement		VARCHAR (32)
	,C_Notes   			VARCHAR (32)
)
BEGIN
	DECLARE C_NewLines2 char(4);
	DECLARE C_NewLines1 char(2);
	DECLARE C_temp varchar(4096);
	IF C_Notes is null THEN
   		SET C_Notes := '';
  	END IF;
  	IF C_Measurement is null THEN
   		SET C_Measurement := 'Missing';
  	END IF;

	IF LENGTH(C_Measurement) = 0 THEN
   		SET C_Measurement := 'Missing';
	END IF;
  	
	SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

	UPDATE objtrackerT_Measurement
			SET Measurement = C_Measurement
				,Notes = C_Notes
				,Track_Changed = Current_Timestamp
				,Track_Userid = @UserName
			WHERE OrganizationID = C_CallerOrg AND ID = C_ID;
	
	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
END
$$
