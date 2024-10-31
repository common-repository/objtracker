DROP TRIGGER IF EXISTS objtrackerX_ObjectiveType_Insert;
DROP TRIGGER IF EXISTS objtrackerX_ObjectiveType_Update;
DROP TRIGGER IF EXISTS objtrackerX_ObjectiveType_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_ObjectiveType_Insert AFTER INSERT ON objtrackerT_ObjectiveType
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(New.OrganizationID,@myGuid,@now, New.Track_Userid	,400,1,0, 'A' ,substr(New.Title,1,64)  );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 400, 1, 405, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_char(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 400, 1, 401, 'A', 'A', '',New.ID );
	CALL objtrackerP_Audit_bitchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 400, 1, 402, 'A', 'A',New.Active );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 400, 1, 451, 'A', 'A', '',New.Title );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 400, 1, 452, 'A', 'A', '',New.Description );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_ObjectiveType_Update AFTER UPDATE ON objtrackerT_ObjectiveType
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, New.Track_Userid	,400,1,0, 'U' ,substr(Old.Title,1,64) 		 );
	
  if New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_char(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 400, 1, 401, 'U', 'U',Old.ID, New.ID );
	END IF;
	if New.Active <> Old.Active THEN
		CALL objtrackerP_Audit_bit2char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 400, 1, 402, 'U', 'U',Old.Active, New.Active );
	END IF;
	if New.Title <> Old.Title THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 400, 1, 451, 'U', 'U',Old.Title, New.Title );
	END IF;
	if New.Description <> Old.Description THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 400, 1, 452, 'U', 'U',Old.Description, New.Description );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_ObjectiveType_Delete AFTER DELETE ON objtrackerT_ObjectiveType
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 400, 1,0, 'D' ,substr(Old.Title,1,64) );		
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 400, 1, 405,'D','D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_char(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 400, 1, 401, 'D', 'D',Old.ID );
	CALL objtrackerP_Audit_Delete_bitchar( Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 400, 1, 402, 'D', 'D',Old.Active );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 400, 1, 451, 'D', 'D','',Old.Title );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 400, 1, 452, 'D', 'D','',Old.Description );
END
$$
CALL objtrackerP_Audit_Define_table(	400,'objtrackerT_ObjectiveType', 'Objective type', 'This table lists the business terms for objective types.' );
CALL objtrackerP_Audit_Define_Column( 400,405,'e','OrganizationID', 'OrganizationID',	'The organization ID');
CALL objtrackerP_Audit_Define_Column( 400,401,'e','ID', 'ID', 'The unique row identifier of the ObjectiveType table for an organization' );
CALL objtrackerP_Audit_Define_Column( 400,402,'e','Active', 'Active', 'Active=Yes rows are selectable for common usage, Active=No required for maintaining history' );
CALL objtrackerP_Audit_Define_Column( 400,451,'e','Title', 'Title', 'Common name of this objective type' );
CALL objtrackerP_Audit_Define_Column( 400,452,'e','Description', 'Description', 'Additional detail describing this objective type' );
CALL objtrackerP_Audit_Define_Column( 400,491,'e','Track_Changed', 'Last changed', 'Time that this row was last changed' );
CALL objtrackerP_Audit_Define_Column( 400,492,'e','Track_Userid', 'Changed by', 'User name of the last change' );
