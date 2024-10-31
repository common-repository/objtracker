DROP PROCEDURE IF EXISTS objtrackerP_Audit_DocumentColumn;
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_DocumentColumn (
	C_CallerOrg			INT 
	,C_CallerUser		INT 
	,C_TableName		VARCHAR (64)
)
BEGIN
	SELECT 
			objtrackerT_AuditColumn.ID AS C_ID
			,objtrackerT_AuditColumn.Name AS C_CName
			,objtrackerT_AuditColumn.DataType AS C_DataType 
			,objtrackerT_AuditColumn.Description AS C_Description
			,objtrackerT_AuditColumn.Documentation AS C_Documentation 
		FROM objtrackerT_AuditTable 
		JOIN objtrackerT_AuditColumn ON objtrackerT_AuditTable.ID = objtrackerT_AuditColumn.TableID 
		WHERE objtrackerT_AuditTable.Name = C_TableName
		ORDER BY objtrackerT_AuditColumn.ID; 
END
$$
