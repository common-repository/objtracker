DROP PROCEDURE IF EXISTS objtrackerP_Documentation;
DELIMITER $$

CREATE PROCEDURE objtrackerP_Documentation (
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_ID			INT 
)
BEGIN
 	SELECT 
		FileName AS C_Filename
		,MimeType AS C_MimeType
		,ObjectiveID AS C_ObjectiveID
		,PeriodStarting AS C_PeriodStarting
	FROM objtrackerT_Documentation 
	WHERE OrganizationID = C_CallerOrg AND ID = C_ID ;
END
$$
