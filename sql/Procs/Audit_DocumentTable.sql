DROP PROCEDURE IF EXISTS objtrackerP_Audit_DocumentTable;
DELIMITER $$
/*
	EXEC objtrackerP_Audit_DocumentTable 'objtrackerT_Frequency'
*/
CREATE PROCEDURE objtrackerP_Audit_DocumentTable (
	C_CallerOrg			INT 
	,C_CallerUser		INT 
	,C_TableName		VARCHAR (32)
)
BEGIN
		SELECT
			objtrackerT_AuditTable.Name AS C_TName 
			,objtrackerT_AuditTable.Description AS C_Description
			,objtrackerT_AuditTable.Documentation AS C_Documentation
		FROM objtrackerT_AuditTable
		WHERE objtrackerT_AuditTable.Name = C_TableName;
END
$$
