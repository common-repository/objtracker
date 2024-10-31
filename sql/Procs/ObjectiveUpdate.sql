DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveUpdate;
/*
		Revised: 2012/12/03 Update should not change either frequency or metric type!
*/
/*
	call objtrackerP_ObjectiveUpdate (13, 2011,2012,2012,2012,1,'Public','source','C','nnn','a1111','me')
	call objtrackerP_ObjectiveUpdate (13, 2012,2012,2011,2012,1,'Public','source','C','nnn','a1111','me')
	SELECT * from objtrackerT_Target where ID = 13
	SELECT * from objtrackerT_Measurement where ObjectiveID = 13
	SELECT * from objtrackerT_Objective where ID = 13
*/
DELIMITER $$
CREATE PROCEDURE objtrackerP_ObjectiveUpdate(
	C_CallerOrg				INT
	,C_CallerUser			INT
	,C_ID					INT
	,C_FiscalYear1			INT			
	,C_FiscalYear2			INT
	,C_WasFiscalYear1		INT			
	,C_WasFiscalYear2		INT
	,C_OwnerID				INT	
	,C_IsPublic				varchar(16) 		
	,C_Source 				varchar(100)	
	,C_TypeID				CHAR	
	,C_Title				varchar(100)
	,C_Description			varchar(1024)	
)
BEGIN
	-- Get facts from 1st existing row of objtrackerT_Target
	DECLARE C_Target ,C_Target1, C_Target2 varchar(16);
	DECLARE C_bPublic BIT;

	SELECT  Target, Target1, Target2 INTO C_Target, C_Target1, C_Target2
		FROM objtrackerT_Target
		WHERE OrganizationID = C_CallerOrg AND ID = C_ID AND FiscalYear = C_WasFiscalYear1;

	IF C_IsPublic = 'Public' THEN
		SET C_bPublic := 1;
	ELSE
		SET C_bPublic := 0;
	END IF;
	IF C_Source IS NULL THEN
		SET @Source := '';
	ELSE
		SET @Source := C_Source;
	END IF;

	IF (C_Title IS NULL OR C_Title = '') THEN
		SELECT 'ObjUpdTitle' AS C_ErrorID, 'Title field is required' AS C_ErrorMessage;
	ELSEIF (C_Description IS NULL OR C_Description = '') THEN
		SELECT 'ObjUpdDesc' AS C_ErrorID, 'Descripton field is required' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_Objective WHERE OrganizationID = C_CallerOrg AND Title = C_Title AND ID <> C_ID) THEN
		SELECT 'ObjUpdDupTitle' AS C_ErrorID, 'Title already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

		-- Update objtrackerT_Objective
		UPDATE objtrackerT_Objective SET
			FiscalYear1			= C_FiscalYear1
			,FiscalYear2		= C_FiscalYear2
			,Title				= C_Title
			,Description		= C_Description
			,IsPublic			= C_bPublic
			,TypeID				= C_TypeID
			,OwnerID			= C_OwnerID
			,Source				= @Source
			,Track_Changed		= Now()
			,Track_Userid		= @UserName
		WHERE OrganizationID = C_CallerOrg AND objtrackerT_Objective.ID = C_ID;

		-- Expand or contract FiscalYear and Measurements
		CALL objtrackerP_Sub_ExtendFY( C_CallerOrg,C_CallerUser, C_ID,C_FiscalYear1,C_WasFiscalYear1,C_FiscalYear2,C_WasFiscalYear2);
		-- If beginning fiscal year advanced, delete now unneeded
		IF C_FiscalYear1 > C_WasFiscalYear1 THEN
			DELETE FROM objtrackerT_Target WHERE OrganizationID = C_CallerOrg AND ID = C_ID AND FiscalYear < C_FiscalYear1;
		END IF;
		-- If ending fiscal year lowered, delete now unneeded
		IF C_FiscalYear2 < C_WasFiscalYear2 THEN
			DELETE FROM objtrackerT_Target WHERE OrganizationID = C_CallerOrg AND ID = C_ID AND FiscalYear > C_FiscalYear2;
		END IF;
	
		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
