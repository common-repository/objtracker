DROP PROCEDURE IF EXISTS objtrackerP_Audit_DocumentTables;
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_DocumentTables(
	C_CallerOrg				INT 
	,C_CallerUser			INT 
)
BEGIN
		SELECT 
			Id AS C_ID
			,Name AS C_TableName
			,Description AS C_Description
		FROM  objtrackerT_AuditTable  
		ORDER BY C_Description;
END
$$
