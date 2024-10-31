DROP PROCEDURE IF EXISTS objtrackerP_Audit_IndexItem;
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_IndexItem(
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	,C_SKey CHAR(64)
)
BEGIN
	DECLARE C_today,C_3months,C_week DateTime;
	SET C_today 		:= Date_Add( now(), INTERVAL-1 DAY);
	SET C_3months 	  := Date_Add( now(), INTERVAL -3 MONTH);
	SET C_week 		:= Date_Add( now(), INTERVAL-7 DAY); 

	SELECT 
		objtrackerT_AuditIndex.Track_Date AS C_Track_Date ,
		objtrackerT_AuditIndex.Track_Guid AS C_ID,
		objtrackerT_AuditIndex.Track_Guid AS C_SKey,
		objtrackerT_AuditIndex.Track_Userid AS C_Track_UserID,
		objtrackerT_AuditIndex.Track_Action AS C_Track_Action,
		CASE objtrackerT_AuditIndex.Track_Action
			WHEN 'U' THEN 'Update'
			WHEN 'D' THEN 'Delete'
			ELSE 'New' END AS C_Action ,
		objtrackerT_AuditTable.Description AS C_TableName,
		objtrackerT_AuditTable.Documentation AS C_Documentation,
		objtrackerT_AuditIndex.Track_Name AS C_Name
	FROM objtrackerT_AuditIndex,objtrackerT_AuditTable
	WHERE objtrackerT_AuditIndex.Track_TableID = objtrackerT_AuditTable.ID 
	  AND objtrackerT_AuditIndex.Track_Guid = C_SKey
	ORDER BY objtrackerT_AuditIndex.Track_Date DESC, objtrackerT_AuditIndex.Track_Userid ;
END
$$
