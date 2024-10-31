DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveInsert;
--	CALL objtrackerP_ObjectiveInsert(2012 ,2012 ,'1' ,'2' ,'3' ,'No' ,'z' ,'C' ,'A','I' ,'1' ,'zzz' ,'z' ,'Admin')
--	CALL objtrackerP_ObjectiveInsert(2012,2012,'1','5','1','Public','Source','F','A','I','9','title','desc','me')
--	EXEC objtrackerP_ObjectiveInsert '2012','2012','5','G','Y','Public','2','F','Q','$','target','title','description','me'
DELIMITER $$
CREATE PROCEDURE objtrackerP_ObjectiveInsert (
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_FiscalYear1		INT			
	,C_FiscalYear2		INT
	,C_OwnerID			varchar(16)	
	,C_Target1 			varchar(16) -- For objtrackerT_Target
	,C_Target2	 		varchar(16) -- For objtrackerT_Target
	,C_IsPublic			varchar(16) 		
	,C_Source 			varchar(100)	
	,C_TypeID			varchar(16)	
	,C_FrequencyID		varchar(16)			
	,C_MetricTypeID		varchar(16)	
	,C_Target			varchar(16)  -- For objtrackerT_Target	
	,C_Title			varchar(100)
	,C_Description		Varchar(1024)	
)
BEGIN
	-- Translate Bits
	IF C_IsPublic = 'Yes' THEN
		SET @IsPublic := 1;
	ELSE
		SET @IsPublic := 0;
	END IF;

	IF (C_Title IS NULL OR C_Title = '') THEN
		SELECT 'ObjInsTitle' AS C_ErrorID, 'Title field is required' AS C_ErrorMessage;
	ELSEIF (C_Description IS NULL OR C_Description = '') THEN
		SELECT 'ObjInsDesc' AS C_ErrorID, 'Descripton field is required' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_Objective WHERE OrganizationID = C_CallerOrg AND Title = C_Title) THEN
		SELECT 'ObjInsDupTitle' AS C_ErrorID, 'Title already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

		-- Get 1st unused ID in objtrackerT_Objective
		SELECT MAX(ID)+1 INTO @ID FROM objtrackerT_Objective WHERE OrganizationID = C_CallerOrg;
		IF @ID IS NULL THEN
			SET @ID := 1;
		END IF;
		-- Add row to objtrackerT_Objective
		INSERT INTO objtrackerT_Objective (
			ID
			,OrganizationID
			,FiscalYear1,FiscalYear2	
			,Title,Description	
			,IsPublic
			,TypeID
			,OwnerID,Source
			,FrequencyID
			,MetricTypeID
			,Track_Changed,Track_Userid		
		) VALUES (
			@ID
			,C_CallerOrg
			,C_FiscalYear1
			,C_FiscalYear2
			,C_Title
			,C_Description
			,@IsPublic
			,C_TypeID
			,C_OwnerID	
			,C_Source
			,C_FrequencyID
			,C_MetricTypeID
			,now()		
			,@UserName
		);

		-- Add rows to objtrackerT_Target
		WHILE C_FiscalYear1 <= C_FiscalYear2 DO
			INSERT INTO objtrackerT_Target (
				ID
				,OrganizationID
				,FiscalYear	
				,Target,Target1 ,Target2 	
				,Track_Changed,Track_Userid		
			) VALUES (
				@ID
				,C_CallerOrg
				,C_FiscalYear1
				,C_Target,C_Target1,C_Target2
				,Current_Timestamp,@UserName
			);
			SET C_FiscalYear1 := C_FiscalYear1 + 1;
		END WHILE;
		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
