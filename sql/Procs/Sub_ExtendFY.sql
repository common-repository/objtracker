DROP PROCEDURE IF EXISTS objtrackerP_Sub_ExtendFY;
DELIMITER $$
/*
	call objtrackerP_Sub_ExtendFY (1,1,1, 2011,2012,2012,2012)
	call objtrackerP_Sub_ExtendFY (1,1,1, 2012,2011,2012,2012)
	SELECT * from objtrackerT_Target where ID = 1
	SELECT * from objtrackerT_Measurement where ID = 1
	SELECT * from objtrackerT_Objective where ID = 1
*/
CREATE PROCEDURE objtrackerP_Sub_ExtendFY (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	,C_ID					INT 
	,C_FiscalYear1			INT
	,C_WasFiscalYear1		INT
	,C_FiscalYear2			INT
	,C_WasFiscalYear2		INT
)
BEGIN
	SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

	-- Get facts from 1st existing row of objtrackerT_Target
	SELECT Target,Target1,Target2 INTO @Target,@Target1,@Target2 
		FROM objtrackerT_Target WHERE OrganizationID = C_CallerOrg AND ID = C_ID AND FiscalYear = C_WasFiscalYear2;

	-- If prior target info
	IF @Target IS NOT NULL THEN
		-- If update expanded FY to before or after original range
		IF C_FiscalYear1 < C_WasFiscalYear1 OR C_FiscalYear2 > C_WasFiscalYear2 THEN
			SET @FY1 := C_FiscalYear1;
			-- Insert missing rows of fiscal years
			WHILE @FY1 <= C_FiscalYear2 DO
				IF NOT EXISTS (SELECT * FROM objtrackerT_Target WHERE OrganizationID = C_CallerOrg AND ID = C_ID AND FiscalYear = @FY1) THEN
					INSERT INTO objtrackerT_Target (
						OrganizationID
						,ID
						,FiscalYear	
						,Target,Target1,Target2 	
						,Track_Changed,Track_Userid		
					 ) VALUES (
						C_CallerOrg
						,C_ID
						,@FY1
						,@Target,@Target1,@Target2
						,Current_Timestamp,@UserName
					);				
				END IF;
				SET @FY1 := @FY1 + 1;
			END WHILE;
		END IF;
	END IF;

	-- If beginning fiscal year advanced, delete now unneeded
	IF C_FiscalYear1 > C_WasFiscalYear1 THEN
		DELETE FROM objtrackerT_Target WHERE OrganizationID = C_CallerOrg AND ID = C_ID AND FiscalYear < C_FiscalYear1;
	END IF;
	-- If ending fiscal year lowered, delete now unneeded
	IF C_FiscalYear2 < C_WasFiscalYear2 THEN
		DELETE FROM objtrackerT_Target WHERE OrganizationID = C_CallerOrg AND ID = C_ID AND FiscalYear > C_FiscalYear2;
	END IF;
END
$$
