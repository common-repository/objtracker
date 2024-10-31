DROP PROCEDURE IF EXISTS objtrackerP_Audit_Define_Table;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Define_Column;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Add_auditindex;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Varchar;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Bit;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_bit;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Bit2;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_BitChar;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_bitChar;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Char;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_Char;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Bit2Char;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Datetime;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Datetime2;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_DateTime;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Int;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_int;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Int2;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Show_changes;
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Define_Table(
	my_ID	INT
	,my_Name 			VARCHAR (32)
	,my_Description 	VARCHAR (32)
	,my_Documentation	VARCHAR (1024)
) 
BEGIN
	DELETE FROM objtrackerT_AuditTable WHERE ID = my_ID;
	DELETE FROM objtrackerT_AuditColumn WHERE ID > my_ID and ID < my_ID+99;
	
	INSERT INTO objtrackerT_AuditTable 	(
		ID,Name,Description,Documentation)
	VALUES 
		(my_ID,my_Name,my_Description,my_Documentation);
END
$$
DELIMITER $$
/*  
	CALL objtrackerP_Audit_Define_Column(
	200,292,'e','Track_Userid', 'Changed by',	'User name of the last change'
	);
*/
CREATE PROCEDURE objtrackerP_Audit_Define_Column(
	my_tableID 			INT 
	,my_ID				INT
	,my_Type 			VARCHAR (1)
	,my_Name 			VARCHAR (32)
	,my_Description 	VARCHAR (32)
	,my_Documentation 	VARCHAR (1024)
)
BEGIN
	DECLARE my_TableName,my_DataType VARCHAR (32);
	DELETE FROM objtrackerT_AuditColumn WHERE ID = my_ID ;

--	SELECT @@lower_case_table_names INTO @mode;
--	IF @mode = 1 THEN
--		SELECT Lower(NAME) INTO my_TableName FROM objtrackerT_AuditTable WHERE ID = my_TableID;
--  ELSE
--		SELECT NAME INTO my_TableName FROM objtrackerT_AuditTable WHERE ID = my_TableID;
--	END IF;

--	SELECT T2.Data_Type INTO my_DataType
--		FROM Information_schema.tables as T1 INNER JOIN Information_schema.columns as T2 ON T1.table_name = T2.table_name
--		WHERE T1.table_name = my_TableName AND T2.Column_name = my_Name
--      AND T1.table_schema = database() AND T2.table_schema = database();

	INSERT INTO objtrackerT_AuditColumn 	(
		ID,TableID,Type,DataType,Name,Description,Documentation)
	VALUES 
		(my_ID,my_TableId,my_Type,'?',my_Name,my_Description,my_Documentation);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Add_auditindex(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_row 		INT
	,my_PID 		INT
	,my_Action 		VARCHAR (1)
	,my_Name 		VARCHAR (64)
) 
BEGIN
	INSERT INTO objtrackerT_AuditIndex (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,
		Track_ID,
		Track_PID,
		Track_Action,
		Track_Name)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID, 
		my_PID,
		my_Row,
		my_Action, 
		my_Name);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Varchar(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		VARCHAR (1024)
	,my_After 		VARCHAR (1024)
) BEGIN
	INSERT INTO objtrackerT_Audit (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,Track_ID,Track_ColumnID,
		Track_Action,Track_SubAction,
		Track_Before,Track_After)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_Before,my_After);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Bit(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	int
	,my_ID 			int
	,my_ColumnID 	int
	,my_Action		VARCHAR (1)
	,my_SubAction	VARCHAR (1)
	,my_Before bit   
)
BEGIN
	DECLARE my_bc VARCHAR(16);
	IF my_Before = 1 THEN
		SET my_bc := 'True';
	ELSE
		SET my_bc := 'False';
	END IF;
	INSERT INTO objtrackerT_Audit (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,Track_ID,Track_ColumnID,
		Track_Action,Track_SubAction,
		Track_Before,Track_After)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_bc,'');
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Delete_bit(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID		INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		bit   
) 
BEGIN
	DECLARE my_bc VARCHAR(16);
	IF my_Before = 1 THEN
		SET my_bc := 'True';
	ELSE
		SET my_bc := 'False';
	END IF;
	INSERT INTO objtrackerT_Audit (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,Track_ID,Track_ColumnID,
		Track_Action,Track_SubAction,
		Track_Before,Track_After)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_bc,'');
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Bit2(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		bit  
	,my_After 		bit  
) 
BEGIN
	DECLARE my_bc,my_ac varchar(16);
	IF my_Before = 1 THEN
		SET my_bc := 'True';
	ELSE
		SET my_bc := 'False';
	END IF;
	IF my_after = 1 THEN
		SET my_ac := 'True';
	ELSE
		SET my_ac := 'False';
	END IF;
	INSERT INTO objtrackerT_Audit (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,Track_ID,Track_ColumnID,
		Track_Action,Track_SubAction,
		Track_Before,Track_After)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_bc,my_ac);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_BitChar(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_After 		bit   
) 
BEGIN
	DECLARE my_ac VARCHAR(16);
    
	IF my_After = 1 THEN
		SET my_ac = 'True';
	ELSE
		SET my_ac = 'False';
	END IF;
	INSERT INTO objtrackerT_Audit (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,Track_ID,Track_ColumnID,
		Track_Action,Track_SubAction,
		Track_Before,Track_After)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		'',my_ac);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Delete_bitChar(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		bit   
) 
BEGIN
	DECLARE my_bc VARCHAR(16);
	IF my_Before = 1 THEN
		SET my_bc = 'True';
	ELSE
		SET my_bc = 'False';
	END IF;
	INSERT INTO objtrackerT_Audit (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,Track_ID,Track_ColumnID,
		Track_Action,Track_SubAction,
		Track_Before,Track_After)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_bc,'');
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Char(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid	VARCHAR(16)
	,my_TableID 	INT
	,my_ID		INT
	,my_ColumnID 	INT
	,my_Action 	VARCHAR (1)
	,my_SubAction VARCHAR (1)
	,my_Before 	char(1)   
	,my_After 	char(1)   
) 
BEGIN
	INSERT INTO objtrackerT_Audit (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,Track_ID,Track_ColumnID,
		Track_Action,Track_SubAction,
		Track_Before,Track_After)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_Before,my_After);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Delete_Char(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		char(1)     
) 
BEGIN
	INSERT INTO objtrackerT_Audit (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,Track_ID,Track_ColumnID,
		Track_Action,Track_SubAction,
		Track_Before,Track_After)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_Before,'');
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Bit2Char(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction	VARCHAR (1)
	,my_Before 		bit  
	,my_After 		bit  
) 
BEGIN
	DECLARE my_bc,my_ac varchar(16);
	IF my_Before = 1 THEN
		SET my_bc := 'True';
	ELSE
		SET my_bc := 'False';
	END IF;
	IF my_after = 1 THEN
		SET my_ac := 'True';
	ELSE
		SET my_ac := 'False';
	END IF;
	INSERT INTO objtrackerT_Audit (
		Track_CallerOrg,
		Track_Guid,
	 	Track_Date,Track_Userid,
		Track_TableID,Track_ID,Track_ColumnID,
		Track_Action,Track_SubAction,
		Track_Before,Track_After)
	VALUES(
		my_CallerOrg,
		my_Guid,
		my_Date,my_Userid,
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_bc,my_ac);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Datetime(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		DateTime 
) 
BEGIN
	DECLARE  my_Before2 VARCHAR (1024);
	IF my_Before IS NULL THEN
		SET my_Before2 := '';
	ELSE
		SET my_Before2 := CAST(my_Before AS CHAR(32)) ;
	END IF;
		
	CALL objtrackerP_Audit_Varchar	(	
		my_CallerOrg,my_Guid,my_Date,my_Userid,	
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		'',my_Before2);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Datetime2(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		DateTime 
	,my_After 		DateTime 
) 
BEGIN
	DECLARE  my_Before2,my_After2 varchar (1024);
	IF my_Before IS NULL or my_Before = '1900/01/01' THEN
		SET my_Before2 := '';
	ELSE
		SET my_Before2 :=  CAST(my_Before AS CHAR(32)) ;
	END IF;
	IF my_After IS NULL or my_After = '1900/01/01' THEN
		SET my_After2 := '';
	ELSE
		SET my_After2 := CAST(my_After AS CHAR(32)) ;
	END IF;
		
	CALL objtrackerP_Audit_Varchar	(	
		my_CallerOrg,	my_Guid,my_Date,my_Userid,	
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_Before2,my_After2);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Delete_DateTime(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		DateTime 
) 
BEGIN
	DECLARE  my_Before2 VARCHAR (1024);
	IF my_Before IS NULL THEN
		SET my_Before2 := '' ;
	ELSE
		SET my_Before2 :=  CAST(my_Before AS CHAR(32)) ;
	END IF ;
		
	CALL objtrackerP_Audit_Varchar	(		
		my_CallerOrg,my_Guid,my_Date,my_Userid,	
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_Before2,'');
END
$$
DELIMITER $$
/*	DECLARE my_now DateTime
	select my_now = GetDate()
	CALL objtrackerP_Audit_Int my_now, 'userid',1,2, 3,'A','A', 4
*/  
CREATE PROCEDURE objtrackerP_Audit_Int(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		INT 
) 
BEGIN
	DECLARE  my_Before2,my_After2 VARCHAR (1024);
	IF my_Before IS NULL THEN
		SET my_Before2 := '';
	ELSE
		SET my_Before2 :=  CAST(my_Before AS CHAR(32)) ;
	END IF;
	CALL objtrackerP_Audit_Varchar	(		
		my_CallerOrg,my_Guid,my_Date,my_Userid,	
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		'',my_Before2);
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Delete_int(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		INT 
) 
BEGIN
	DECLARE my_Before2,my_After2 VARCHAR (1024);
	IF my_Before IS NULL THEN
		SET my_Before2 := '';
	ELSE
		SET my_Before2 :=  CAST(my_Before AS CHAR(32)) ;
	END IF;
		
	CALL objtrackerP_Audit_Varchar	(		
		my_CallerOrg,my_Guid,my_Date,my_Userid,	
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_Before2,'');
END
$$
DELIMITER $$
CREATE PROCEDURE objtrackerP_Audit_Int2(
	my_CallerOrg		INT
	,my_Guid 		VARCHAR(64)   
	,my_Date		DateTime
	,my_Userid		VARCHAR(16)
	,my_TableID 	INT
	,my_ID 			INT
	,my_ColumnID 	INT
	,my_Action 		VARCHAR (1)
	,my_SubAction 	VARCHAR (1)
	,my_Before 		INT 
	,my_After 		INT 
) 
BEGIN
	DECLARE my_Before2 ,my_After2 VARCHAR (1024);
	IF my_Before IS NULL THEN
		SET my_Before2 := '';
	ELSE
		SET my_Before2 :=  CAST(my_Before AS CHAR(32)) ;
	END IF;
	IF my_After IS NULL THEN
		SET my_After2 := '';
	ELSE
		SET my_After2 :=  CAST(my_After AS CHAR(32)) ;
	END IF;
	CALL objtrackerP_Audit_Varchar (		
		my_CallerOrg,my_Guid,my_Date,my_Userid,	
		my_TableID,my_ID,my_ColumnID,
		my_Action,my_SubAction,
		my_Before2,my_After);
END
$$
DELIMITER $$
/* DECLARE my_Date DateTime
	select my_Date = DateAdd(day,-1,GETDATE())
	CALL objtrackerP_Audit_Show_changes my_Date = my_Date
*/
/* DECLARE my_Date DateTime
	select my_Date = DateAdd(day,-10,GETDATE())
	CALL objtrackerP_Audit_Show_changes my_Date = my_Date,my_Userid = ''
*/
CREATE PROCEDURE objtrackerP_Audit_Show_changes(
	my_Date		DateTime  
	,my_Userid	VARCHAR(16) 
) 
BEGIN
	IF my_Date IS NULL THEN
		IF	my_Userid	IS NULL THEN
			SELECT 
				objtrackerT_Audit.Track_Date,
				objtrackerT_Audit.Track_Userid ,
				objtrackerT_AuditTable.Name as TableName,
				objtrackerT_Audit.Track_ID as '#',objtrackerT_AuditColumn.Name as ColumnName,
				objtrackerT_AuditColumn.Description as Description
			FROM objtrackerT_Audit,objtrackerT_AuditTable,objtrackerT_AuditColumn
			WHERE objtrackerT_Audit.Track_TableID = objtrackerT_AuditTable.Id 
			AND objtrackerT_Audit.Track_ColumnID = objtrackerT_AuditColumn.ID 
			ORDER BY Track_Date,Track_Userid,TableName,'#',ColumnName;
		ELSE
			SELECT 
				objtrackerT_Audit.Track_Date,
				objtrackerT_Audit.Track_Userid ,
				objtrackerT_AuditTable.Name as TableName,
				objtrackerT_Audit.Track_ID as '#',objtrackerT_AuditColumn.Name as ColumnName,
				objtrackerT_AuditColumn.Description as Description
			FROM objtrackerT_Audit,objtrackerT_AuditTable,objtrackerT_AuditColumn
			WHERE objtrackerT_Audit.Track_TableID = objtrackerT_AuditTable.Id 
			AND objtrackerT_Audit.Track_ColumnID = objtrackerT_AuditColumn.ID 
			AND objtrackerT_Audit.Track_Userid = my_userId
			ORDER BY Track_Date,Track_Userid,TableName,'#',ColumnName;
		END IF;
	ELSE
		IF	my_Userid	IS NULL THEN
			SELECT 
				objtrackerT_Audit.Track_Date,
				objtrackerT_Audit.Track_Userid ,
				objtrackerT_AuditTable.Name as TableName,
				objtrackerT_Audit.Track_ID as '#',objtrackerT_AuditColumn.Name as ColumnName,
				objtrackerT_AuditColumn.Description as Description
			FROM objtrackerT_Audit,objtrackerT_AuditTable,objtrackerT_AuditColumn
			WHERE objtrackerT_Audit.Track_TableID = objtrackerT_AuditTable.Id 
			AND objtrackerT_Audit.Track_ColumnID = objtrackerT_AuditColumn.ID 
			AND objtrackerT_Audit.Track_Date > my_Date
			ORDER BY Track_Date,Track_Userid,TableName,'#',ColumnName;
		ELSE
			SELECT 
				objtrackerT_Audit.Track_Date,
				objtrackerT_Audit.Track_Userid ,
				objtrackerT_AuditTable.Name as TableName,
				objtrackerT_Audit.Track_ID as '#',objtrackerT_AuditColumn.Name as ColumnName,
				objtrackerT_AuditColumn.Description as Description
			FROM objtrackerT_Audit,objtrackerT_AuditTable,objtrackerT_AuditColumn
			WHERE objtrackerT_Audit.Track_TableID = objtrackerT_AuditTable.Id 
			AND objtrackerT_Audit.Track_ColumnID = objtrackerT_AuditColumn.ID 
			AND objtrackerT_Audit.Track_Userid = my_userId
			AND objtrackerT_Audit.Track_Date > my_Date
			ORDER BY Track_Date,Track_Userid,TableName,'#',ColumnName;
		END IF;
	END IF;
END
$$
DELIMITER $$
