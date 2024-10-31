DROP PROCEDURE IF EXISTS objtrackerP_DocumentationList;
DELIMITER $$
		  
CREATE PROCEDURE objtrackerP_DocumentationList (
	C_CallerOrg			INT 
	,C_CallerUser		INT 
	,C_ObjectiveID		INT
	,C_PeriodStarting	VARCHAR(16)
)
BEGIN
	SELECT 
		ID AS C_ID
		,ObjectiveID AS C_ObjectiveID
		,Description AS C_Description
		,PeriodStarting AS C_PeriodStarting
		,FileName AS C_FileName
		,MimeType AS C_MimeType
		,objtrackerF_FormatDate(Track_Added) AS C_Track_Added
		,objtrackerF_FormatSortedDate(Track_Added) AS C_Track_SortedAdded
		,Track_Userid AS C_Track_Userid
	FROM objtrackerT_Documentation 
	WHERE ObjectiveID = C_ObjectiveID AND PeriodStarting = C_PeriodStarting
	  AND OrganizationID = C_CallerOrg 
	ORDER BY ID;
END
$$
