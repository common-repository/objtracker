DROP TRIGGER IF EXISTS objtrackerX_Department_Insert;
DROP TRIGGER IF EXISTS objtrackerX_Department_Update;
DROP TRIGGER IF EXISTS objtrackerX_Department_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_Department_Insert AFTER INSERT ON objtrackerT_Department
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(New.OrganizationID,	@myGuid,@now, New.Track_Userid	,200,1,0, 'A' ,New.Title );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 205, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 201, 'A', 'A',New.ID );
	CALL objtrackerP_Audit_bitchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 202, 'A', 'A',New.Active );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 251, 'A', 'A', '',New.Title );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 252, 'A', 'A', '',New.Title2 );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 253, 'A', 'A',New.ParentID );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Department_Update AFTER UPDATE ON objtrackerT_Department 
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, New.Track_Userid	,200,1,0, 'U' ,Old.Title  );		
	IF New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 201, 'U', 'U',Old.ID, New.ID );
	END IF;
	IF New.Active <> Old.Active THEN
		CALL objtrackerP_Audit_bit2char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 202, 'U', 'U',Old.Active, New.Active );
	END IF;
	IF New.Title <> Old.Title THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 251, 'U', 'U',Old.Title, New.Title );
	END IF;
	IF New.Title2 <> Old.Title2 THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 252, 'U', 'U',Old.Title2, New.Title2 );
	END IF;
	IF New.ParentID <> Old.ParentID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 200, 1, 253, 'U', 'U',Old.ParentID, New.ParentID );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Department_Delete AFTER DELETE ON objtrackerT_Department 
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 200,1,0,'D',Old.Title 	 );	
	CALL objtrackerP_Audit_Delete_int(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 200,1,205,'D','D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_int(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 200,1,201,'D','D',Old.ID );
	CALL objtrackerP_Audit_Delete_bitchar( 	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 200,1,202,'D','D',Old.Active );
	CALL objtrackerP_Audit_varchar(			Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 200,1,251,'D','D',Old.Title,''  ); -- old,new
	CALL objtrackerP_Audit_varchar(			Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 200,1,252,'D','D',Old.Title2,'' );
	CALL objtrackerP_Audit_Delete_int(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 200,1,253,'D','D',Old.ParentID );
END
$$
CALL objtrackerP_Audit_Define_table (
	200,'objtrackerT_Department', 'Department',	'This table lists business units that have objectives.'
	);
CALL objtrackerP_Audit_Define_Column(
	200,201,'e','ID', 'ID',	'The unique row identifier of the Department table for an organization'
	);
CALL objtrackerP_Audit_Define_Column(
	200,205,'e','OrganizationID', 'OrganizationID',	'The organization ID'
	);
CALL objtrackerP_Audit_Define_Column(
	200,202,'e','Active', 'Active',	'Active=Yes rows are selectable for common usage, Active=No required for maintaining history'
	);
CALL objtrackerP_Audit_Define_Column(
	200,251,'e','Title', 'Title',	'Common name of this business unit'
	);
CALL objtrackerP_Audit_Define_Column(
	200,252,'e','Title2', 'Title2',	'Short name of this business unit'
	);
CALL objtrackerP_Audit_Define_Column(
	200,253,'e','ParentID', 'Parent',	'Department above this one'
	);
CALL objtrackerP_Audit_Define_Column(
	200,291,'e','Track_Changed', 'Last changed',	'Time that this row was last changed'
	);
CALL objtrackerP_Audit_Define_Column(
	200,292,'e','Track_Userid', 'Changed by',	'User name of the last change'
	);
