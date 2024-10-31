DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveTypeList;
DELIMITER $$
/*
	CALL objtrackerP_ObjectiveTypeList
*/
CREATE PROCEDURE objtrackerP_ObjectiveTypeList(
	C_CallerOrg				INT
	,C_CallerUser			INT
)BEGIN
	SELECT 
		ID AS C_TypeID
		,ID AS C_ID
		,Title AS C_Title, Description AS C_Description
		,(SELECT COUNT(*) FROM objtrackerT_Objective WHERE OrganizationID = C_CallerOrg AND TypeID = objtrackerT_ObjectiveType.ID) AS C_Usage	
		,(SELECT objtrackerF_FormatDate(Track_Changed)) AS C_Track_Changed
		,(SELECT objtrackerF_FormatSortedDate(Track_Changed)) AS C_Track_SortedChanged
		,Track_Userid AS C_Track_Userid
	FROM objtrackerT_ObjectiveType
	WHERE OrganizationID = C_CallerOrg
	ORDER BY Title;
END
$$
