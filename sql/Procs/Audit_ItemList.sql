DROP PROCEDURE IF EXISTS objtrackerP_Audit_ItemList;
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_ItemList (
	C_CallerOrg		INT 
	,C_CallerUser	INT 
	,C_SKey 		CHAR(64))
BEGIN 
	DECLARE C_mDate datetime;
	DECLARE C_userid varchar(32);
	DECLARE C_Id int;
	DECLARE C_mystring varchar(64);
 
		SELECT 
			objtrackerT_AuditColumn.Name AS C_Element,
			objtrackerT_AuditColumn.Description AS C_ID,
			objtrackerT_AuditColumn.Description AS C_Description,
			objtrackerT_AuditColumn.Documentation AS C_Documentation,
			objtrackerT_Audit.Track_Before AS C_Value1,
			objtrackerT_Audit.Track_After AS C_Value2
		FROM objtrackerT_Audit,objtrackerT_AuditTable,objtrackerT_AuditColumn
		WHERE objtrackerT_Audit.Track_Guid = C_SKey
		 AND	objtrackerT_Audit.Track_TableID = objtrackerT_AuditTable.ID 
		 AND objtrackerT_Audit.Track_ColumnID = objtrackerT_AuditColumn.ID 
		ORDER BY objtrackerT_AuditColumn.Name;
END
$$
