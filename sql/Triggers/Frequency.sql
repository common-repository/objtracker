DROP TRIGGER IF EXISTS objtrackerX_Frequency_Insert;
DROP TRIGGER IF EXISTS objtrackerX_Frequency_Update;
DROP TRIGGER IF EXISTS objtrackerX_Frequency_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_Frequency_Insert AFTER INSERT ON objtrackerT_Frequency
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(New.OrganizationID,@myGuid,@now, New.Track_Userid	,500,1,0, 'A' ,New.Title  );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 500, 1, 505, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_char(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 500, 1, 501, 'A', 'A', '',New.ID );
	CALL objtrackerP_Audit_bitchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 500, 1, 502, 'A', 'A',New.Active );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 500, 1, 551, 'A', 'A', '',New.Title );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 500, 1, 552, 'A', 'A', '',New.Description );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Frequency_Update AFTER UPDATE ON objtrackerT_Frequency
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(		Old.OrganizationID,@myGuid,@now, New.Track_Userid	,500,1,0, 'U' ,Old.Title ); 		
	if New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_char(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 500, 1, 501, 'U', 'U',Old.ID, New.ID );
	END IF;
	if New.Active <> Old.Active THEN
		CALL objtrackerP_Audit_bit2char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 500, 1, 502, 'U', 'U',Old.Active, New.Active );
	END IF;
	if New.Title <> Old.Title THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 500, 1, 551, 'U', 'U',Old.Title, New.Title );
	END IF;
	if New.Description <> Old.Description THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 500, 1, 552, 'U', 'U',Old.Description, New.Description );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Frequency_Delete AFTER DELETE ON objtrackerT_Frequency
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 500, 1,0, 'D' ,Old.Title );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 500,1,505,'D','D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_char(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 500, 1, 501, 'D', 'D',Old.ID );
	CALL objtrackerP_Audit_Delete_bitchar(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 500, 1, 502, 'D', 'D',Old.Active );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 500, 1, 551, 'D', 'D',Old.Title,'' );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 500, 1, 552, 'D', 'D',Old.Description,'' );  -- old,new
END
$$
CALL objtrackerP_Audit_Define_table(	500,'objtrackerT_Frequency', 'Frequency', 'This table lists the choices for defining objective''s measurement periods.' );
CALL objtrackerP_Audit_Define_Column( 500,505,'e','OrganizationID', 'OrganizationID',	'The organization ID');
CALL objtrackerP_Audit_Define_Column( 500,501,'e','ID', 'ID', 'The unique row identifier of the Frequency table for an organization' );
CALL objtrackerP_Audit_Define_Column( 500,502,'e','Active', 'Active', 'Active=Yes rows are selectable for common usage, Active=No required for maintaining history' );
CALL objtrackerP_Audit_Define_Column( 500,551,'e','Title', 'Title', 'Common name of this measurement frequency' );
CALL objtrackerP_Audit_Define_Column( 500,552,'e','Description', 'Description', 'Additional detail describing this measurement frequency' );
CALL objtrackerP_Audit_Define_Column( 500,591,'e','Track_Changed', 'Last changed', 'Time that this row was last changed' );
CALL objtrackerP_Audit_Define_Column( 500,592,'e','Track_Userid', 'Changed by', 'User name of the last change' );
