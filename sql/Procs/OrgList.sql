DROP PROCEDURE IF EXISTS objtrackerP_OrgList;
DELIMITER $$
  
/*
	call objtrackerP_OrgList();
*/
CREATE PROCEDURE objtrackerP_OrgList (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	)
BEGIN
	SELECT 
		ID AS C_ID
		,Title AS C_Title
		,UploadFsPath AS C_UploadFsPath
		,objtrackerF_FormatDate(Track_Changed) AS C_Track_Changed
		,objtrackerF_FormatSortedDate(Track_Changed) AS C_Track_SortedChanged
		,Track_Userid AS C_Track_Userid
	FROM objtrackerT_Organization  
	ORDER BY Title;
END
$$
