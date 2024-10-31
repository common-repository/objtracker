DROP PROCEDURE IF EXISTS objtrackerP_FrequencyUpdate;
DELIMITER $$

/*
	EXEC objtrackerP_FrequencyUpdate 1, 'ab','cd',1
*/
CREATE PROCEDURE objtrackerP_FrequencyUpdate (
	C_CallerOrg		INT
	,C_CallerUser	INT
	,C_ID			VARCHAR (2)
	,C_Count_Months	INT	
	,C_WeeksToAlert	INT	
	,C_Title		VARCHAR (32)
	,C_Description	VARCHAR (32)
)
BEGIN
	IF LENGTH(C_Title) = 0 THEN
		SELECT 'FreqUpdTitle' AS C_ErrorID, 'Title not specified' AS C_ErrorMessage;
	ELSEIF LENGTH(C_Description) = 0 THEN
		SELECT 'FreqUpdDesc' AS C_ErrorID, 'Description not specified' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_Frequency WHERE OrganizationID = C_CallerOrg AND Title = C_Title AND ID != C_ID) THEN
		SELECT 'FreqUpdDupTitle' AS C_ErrorID, 'Title already exists' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_Frequency WHERE OrganizationID = C_CallerOrg AND Description = C_Description AND ID != C_ID) THEN
		SELECT 'FreqUpdDupDesc' AS C_ErrorID, 'Description already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

	  	UPDATE objtrackerT_Frequency SET
		  Title = C_Title
		  ,Count_Months = C_Count_Months		
		  ,WeeksToAlert = C_WeeksToAlert		
		  ,Description = C_Description
		  ,Track_Changed = Now()
		  ,Track_Userid = @UserName
	  	WHERE OrganizationID = C_CallerOrg AND ID = C_ID;

		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
 END IF;
END
$$
