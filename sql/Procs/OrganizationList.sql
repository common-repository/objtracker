DROP PROCEDURE IF EXISTS objtrackerP_OrganizationList;
DELIMITER $$
/*
	EXEC objtrackerP_OrganizationList 
*/
CREATE PROCEDURE objtrackerP_OrganizationList (
	C_CallerOrg		INT 
	,C_CallerUser	INT)
BEGIN
	SELECT 
		ID AS C_ID
		,Title AS C_Title
		,ShortTitle AS C_ShortTitle
		,FirstMonth AS C_FirstMonth
		,UploadFsPath AS C_UploadFsPath
		,Trailer AS C_Trailer
		,ChangePassword AS C_ChangePassword
		,Case WHEN ChangePassword = 1 THEN 'Yes' ELSE 'No' END AS C_IsChangePassword
		, (SELECT COUNT(*) FROM objtrackerT_Objective WHERE OrganizationID = objtrackerT_Organization.ID) AS C_Usage
		,objtrackerF_FormatDate(Track_Changed) AS C_Track_Changed
		,objtrackerF_FormatSortedDate(Track_Changed) AS C_Track_SortedChanged
		,Track_Userid AS C_Track_Userid
	FROM objtrackerT_Organization WHERE ID = C_CallerOrg
	ORDER BY Title;
END
$$
