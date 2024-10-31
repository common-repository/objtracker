DROP PROCEDURE IF EXISTS objtrackerP_Audit_IndexList;
DELIMITER $$
/*
	call objtrackerP_Audit_IndexList(1,1,'Who','None','x')
	call objtrackerP_Audit_IndexList(1,1,'All','3Months','x')
*/
CREATE PROCEDURE objtrackerP_Audit_IndexList( 
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_Who 			varchar(16)
	,C_What 		varchar(16)
	,C_Userid 		varchar(48)
)
BEGIN
	DECLARE C_today ,C_3months,C_week DateTime;
	SET C_today 		:= Date_Add( now(), INTERVAL-1 DAY);
	SET C_3months 	  := Date_Add( now(), INTERVAL -3 MONTH);
	SET C_week 		:= Date_Add( now(), INTERVAL-7 DAY); 
	IF C_Who = 'Who' THEN
		IF C_What = 'None' THEN
			SELECT
				objtrackerT_AuditIndex.Track_Userid AS C_ID,
				objtrackerT_AuditIndex.Track_Userid AS C_Track_UserIDC_Track_UserID,
				COUNT(*) AS C_TheCount
			FROM objtrackerT_AuditIndex
			JOIN objtrackerT_AuditTable ON objtrackerT_AuditIndex.Track_TableID = objtrackerT_AuditTable.ID
			WHERE objtrackerT_AuditIndex.Track_Date >= C_3months AND objtrackerT_AuditIndex.Track_CallerOrg = C_CallerOrg
			GROUP BY objtrackerT_AuditIndex.Track_Userid 
			ORDER BY objtrackerT_AuditIndex.Track_Userid DESC
			;
		ELSE
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
			FROM objtrackerT_AuditIndex
			JOIN objtrackerT_AuditTable ON objtrackerT_AuditIndex.Track_TableID = objtrackerT_AuditTable.ID
			WHERE objtrackerT_AuditIndex.Track_Userid = C_What
			 AND objtrackerT_AuditIndex.Track_Date >= C_3months
			 AND objtrackerT_AuditIndex.Track_CallerOrg = C_CallerOrg
			ORDER BY objtrackerT_AuditIndex.Track_Date DESC, objtrackerT_AuditIndex.Track_Userid
			;
		END IF;
	ELSEIF C_Who = 'All' THEN
		IF C_What='Day' THEN
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
			FROM objtrackerT_AuditIndex
			JOIN objtrackerT_AuditTable ON objtrackerT_AuditIndex.Track_TableID = objtrackerT_AuditTable.ID
			WHERE objtrackerT_AuditIndex.Track_Date >= C_today
			 AND objtrackerT_AuditIndex.Track_CallerOrg = C_CallerOrg
			ORDER BY objtrackerT_AuditIndex.Track_Date  DESC,objtrackerT_AuditIndex.Track_Userid
			;
		ELSEIF C_What = '3Months' THEN
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
			FROM objtrackerT_AuditIndex
			JOIN objtrackerT_AuditTable ON objtrackerT_AuditIndex.Track_TableID = objtrackerT_AuditTable.ID
			WHERE objtrackerT_AuditIndex.Track_Date >= C_3months
			  AND objtrackerT_AuditIndex.Track_CallerOrg = C_CallerOrg
			ORDER BY objtrackerT_AuditIndex.Track_Date  DESC,objtrackerT_AuditIndex.Track_Userid
			;
		END IF;
	ELSE -- Mine
		IF C_What= 'Day' THEN
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
			FROM objtrackerT_AuditIndex
			JOIN objtrackerT_AuditTable ON objtrackerT_AuditIndex.Track_TableID = objtrackerT_AuditTable.ID
			WHERE objtrackerT_AuditIndex.Track_Userid = C_Userid
			  AND objtrackerT_AuditIndex.Track_Date >= C_today
			  AND objtrackerT_AuditIndex.Track_CallerOrg = C_CallerOrg
			ORDER BY objtrackerT_AuditIndex.Track_Date  DESC,objtrackerT_AuditIndex.Track_Userid
			;
		ELSEIF C_What= '3Months' THEN
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
			FROM objtrackerT_AuditIndex 
			JOIN objtrackerT_AuditTable ON objtrackerT_AuditIndex.Track_TableID = objtrackerT_AuditTable.ID
			WHERE objtrackerT_AuditIndex.Track_Userid = C_Userid
			   AND objtrackerT_AuditIndex.Track_Date >= C_3months
			   AND objtrackerT_AuditIndex.Track_CallerOrg = C_CallerOrg
			ORDER BY objtrackerT_AuditIndex.Track_Date DESC,objtrackerT_AuditIndex.Track_Userid
			;
		END IF;
	END IF;
END
$$
