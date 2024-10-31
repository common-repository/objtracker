DROP PROCEDURE IF EXISTS objtrackerP_FrequencyList;
DELIMITER $$
/*
	CALL objtrackerP_FrequencyList()
*/
CREATE PROCEDURE objtrackerP_FrequencyList(
	C_CallerOrg				INT
	,C_CallerUser			INT
)
BEGIN
	SELECT
		ID as C_ID
		,Count_Months AS C_Count_Months
		,WeeksToAlert AS C_WeeksToAlert
		,Title AS C_Title
		,Description AS C_Description
		,(SELECT COUNT(*) FROM objtrackerT_Objective WHERE OrganizationID = C_CallerOrg AND FrequencyID = objtrackerT_Frequency.ID) AS C_Usage
		,(SELECT objtrackerF_FormatDate(Track_Changed)) AS C_Track_Changed
		,(SELECT objtrackerF_FormatSortedDate(Track_Changed)) AS C_Track_SortedChanged
		,Track_Userid AS C_Track_Userid
	FROM objtrackerT_Frequency
	WHERE OrganizationID = C_CallerOrg
	ORDER BY Title;
END
$$
