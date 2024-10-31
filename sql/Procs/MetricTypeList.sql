DROP PROCEDURE IF EXISTS objtrackerP_MetricTypeList;
DELIMITER $$
/*
	CALL objtrackerP_MetricTypeList(1,1);
*/
CREATE PROCEDURE objtrackerP_MetricTypeList(
	C_CallerOrg				INT 
	,C_CallerUser			INT 
)
BEGIN
	SELECT 
		ID AS C_ID
		,Title AS C_Title
		,Description AS C_Description
		,Concat(Title, ' ( ', Description, ' ) ')  AS C_FullDescription
		,(SELECT COUNT(*) FROM objtrackerT_Objective WHERE MetricTypeID = objtrackerT_MetricType.ID) AS C_Usage	
		,(SELECT objtrackerF_FormatDate(Track_Changed))  AS C_Track_Changed
		,(SELECT objtrackerF_FormatSortedDate(Track_Changed))  AS C_Track_SortedChanged
		,Track_Userid  AS C_Track_Userid
	FROM objtrackerT_MetricType 
	WHERE OrganizationID = C_CallerOrg 
	ORDER BY C_Title;
END
$$
