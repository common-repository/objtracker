DROP TRIGGER IF EXISTS objtrackerX_FiscalYear_Insert;
DROP TRIGGER IF EXISTS objtrackerX_FiscalYear_Update;
DROP TRIGGER IF EXISTS objtrackerX_FiscalYear_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_FiscalYear_Insert AFTER INSERT ON objtrackerT_FiscalYear
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(New.OrganizationID,@myGuid,@now, New.Track_Userid,300,1,0, 'A' ,New.Title ); 
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 300, 1, 305, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_int(	 New.OrganizationID,@myGuid,@now, New.Track_Userid, 300, 1, 301, 'A', 'A', New.ID );
	CALL objtrackerP_Audit_bitchar(New.OrganizationID,@myGuid,@now, New.Track_Userid, 300, 1, 302, 'A', 'A',New.Active );
	CALL objtrackerP_Audit_varchar(New.OrganizationID,@myGuid,@now, New.Track_Userid, 300, 1, 351, 'A', 'A', '',New.Title );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_FiscalYear_Update AFTER UPDATE ON objtrackerT_FiscalYear
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(	Old.OrganizationID,@myGuid,@now, New.Track_Userid	,300,1,0, 'U' ,Old.Title  );		
	if New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 300, 1, 301, 'U', 'U',Old.ID, New.ID );
	END IF;
	if New.Active <> Old.Active THEN
		CALL objtrackerP_Audit_bit2char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 300, 1, 302, 'U', 'U',Old.Active, New.Active );
	END IF; 
	if New.Title <> Old.Title THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 300, 1, 351, 'U', 'U',Old.Title, New.Title );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_FiscalYear_Delete AFTER DELETE ON objtrackerT_FiscalYear
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 300, 1,0, 'D' ,Old.Title  );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 300,1,305,'D','D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 300, 1, 301, 'D', 'D', Old.ID );
	CALL objtrackerP_Audit_Delete_bitchar(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 300, 1, 302, 'D', 'D',Old.Active );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 300, 1, 351, 'D', 'D',Old.Title,'' );  -- old,new
END
$$
CALL objtrackerP_Audit_Define_table(
	300,'objtrackerT_FiscalYear', 'Fiscal years', 'This table lists the choices fiscal years.' 
	);
CALL objtrackerP_Audit_Define_Column(
	300,305,'e','OrganizationID', 'OrganizationID',	'The organization ID'
	);
CALL objtrackerP_Audit_Define_Column( 
	300,301,'e','ID', 'ID', 'The unique row identifier of the FiscalYear table and organization. Value represents the first year of the fiscal year!'
	);
CALL objtrackerP_Audit_Define_Column( 
	300,302,'e','Active', 'Active', 'Active=Yes rows are selectable for common usage, Active=No required for maintaining history' 
	);
CALL objtrackerP_Audit_Define_Column( 
	300,351,'e','Title', 'Title', 'Alternate spelling for fiscal year' 
	);
CALL objtrackerP_Audit_Define_Column( 
	300,391,'e','Track_Changed', 'Last changed', 'Time that this row was last changed' 
	);
CALL objtrackerP_Audit_Define_Column( 
	300,392,'e','Track_Userid', 'Changed by', 'User name of the last change' 
	);
