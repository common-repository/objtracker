DROP PROCEDURE IF EXISTS objtrackerP_ExtendUpdate;
DELIMITER $$
/*
	call objtrackerP_ExtendUpdate (1, 'Lisa')
	SELECT * from objtrackerT_Target where ID = 1
	SELECT FiscalYear2 from objtrackerT_Objective where ID = 1
*/
CREATE PROCEDURE objtrackerP_ExtendUpdate (
	C_CallerOrg				INT
	,C_CallerUser			INT
	,C_ID					INT
)
BEGIN
	SELECT FiscalYear1 INTO @FiscalYear1 	FROM objtrackerT_Objective WHERE OrganizationID = C_CallerOrg AND ID = C_ID;
	SELECT FiscalYear2 INTO @WasFiscalYear2 FROM objtrackerT_Objective WHERE OrganizationID = C_CallerOrg AND ID = C_ID;
	SET @FiscalYear2 := @WasFiscalYear2 + 1;
	SELECT Max(ID) INTO @MaxFiscalYear 		FROM objtrackerT_FiscalYear WHERE OrganizationID = C_CallerOrg;
	IF @FiscalYear2 > @MaxFiscalYear THEN
		CALL objtrackerP_FiscalYearInsert(C_CallerOrg,C_CallerUser,@FiscalYear2);
	END IF;				

	SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

	-- Update objtrackerT_Objective
	UPDATE objtrackerT_Objective SET
		FiscalYear2			= @FiscalYear2
		,Track_Changed		= Current_Timestamp
		,Track_Userid		= @UserName
	WHERE OrganizationID = C_CallerOrg AND objtrackerT_Objective.ID = C_ID;

	-- Expand or contract FiscalYear and Measurements
	CALL objtrackerP_Sub_ExtendFY(C_CallerOrg,C_CallerUser, C_ID,@FiscalYear1,@FiscalYear1,@FiscalYear2,@WasFiscalYear2);			

	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;

END
$$
