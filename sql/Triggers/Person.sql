DROP TRIGGER IF EXISTS objtrackerX_Person_Insert;
DROP TRIGGER IF EXISTS objtrackerX_Person_Update;
DROP TRIGGER IF EXISTS objtrackerX_Person_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_Person_Insert AFTER INSERT ON objtrackerT_Person
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(New.OrganizationID,@myGuid,@now, New.Track_Userid	,1000,1,0, 'A' ,New.UserName  );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1005, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1001, 'A', 'A',New.ID );
	CALL objtrackerP_Audit_bitchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1002, 'A', 'A',New.Active );
	CALL objtrackerP_Audit_bitchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1003, 'A', 'A',New.Admin );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1051, 'A', 'A', '',New.UserName );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1052, 'A', 'A', '',New.FullName );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1053, 'A', 'A', '',New.Password );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1061, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1062, 'A', 'A',New.DepartmentID );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Person_Update AFTER UPDATE ON objtrackerT_Person
FOR EACH ROW BEGIN
  IF New.UiSettings = Old.UiSettings THEN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, New.Track_Userid	,1000,1,0, 'U' ,Old.UserName );
	if New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1001, 'U', 'U',Old.ID, New.ID );
	END IF;
	if New.Active <> Old.Active THEN
		CALL objtrackerP_Audit_bit2char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1002, 'U', 'U',Old.Active, New.Active );
	END IF;
	if New.Admin <> Old.Admin THEN
		CALL objtrackerP_Audit_bit2char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1003, 'U', 'U',Old.Admin, New.Admin );
	END IF;
	if New.UserName <> Old.UserName THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1051, 'U', 'U',Old.UserName, New.UserName );
	END IF;
	if New.FullName <> Old.FullName THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1052, 'U', 'U',Old.FullName, New.FullName );
	END IF;
	if New.Password <> Old.Password THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1053, 'U', 'U',Old.Password, New.Password );
	END IF;
	if New.OrganizationID <> Old.OrganizationID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1061, 'U', 'U',Old.OrganizationID, New.OrganizationID );
	END IF;
	if New.DepartmentID <> Old.DepartmentID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 1000, 1, 1062, 'U', 'U',Old.DepartmentID, New.DepartmentID );
	END IF;
  END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Person_Delete AFTER DELETE ON objtrackerT_Person
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1,0, 'D' ,Old.UserName );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1, 1005,'D','D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1, 1001, 'D', 'D',Old.ID );
	CALL objtrackerP_Audit_Delete_bitchar(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1, 1002, 'D', 'D',Old.Active );
	CALL objtrackerP_Audit_Delete_bitchar(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1, 1003, 'D', 'D',Old.Admin );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1, 1051, 'D', 'D',Old.UserName,'' );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1, 1052, 'D', 'D',Old.FullName,'' );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1, 1053, 'D', 'D',Old.Password,'' );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1, 1061, 'D', 'D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 1000, 1, 1062, 'D', 'D',Old.DepartmentID );
END
$$
CALL objtrackerP_Audit_Define_table(	1000,'objtrackerT_Person', 'Person', 'This table lists people who can login.' );
CALL objtrackerP_Audit_Define_Column(	1000,1005,'e','OrganizationID', 'OrganizationID',	'The organization ID');
CALL objtrackerP_Audit_Define_Column(	1000,1001,'e','ID', 'ID', 'The unique row identifier of the Person table for an organization' );
CALL objtrackerP_Audit_Define_Column(	1000,1002,'e','Active', 'Active', 'Active=Yes rows are selectable for common usage, Active=No required for maintaining history' );
CALL objtrackerP_Audit_Define_Column(	1000,1003,'e','Admin', 'Administrator', 'Admin=Yes indicates an administrator' );
CALL objtrackerP_Audit_Define_Column(	1000,1051,'e','UserName', 'UserName', 'Login name' );
CALL objtrackerP_Audit_Define_Column(	1000,1052,'e','FullName', 'FullName', 'Login name' );
CALL objtrackerP_Audit_Define_Column(	1000,1053,'e','Password', 'Password', 'User'' encrypted password' );
CALL objtrackerP_Audit_Define_Column(	1000,1061,'e','OrganizationID', 'Organization', 'Person belongs to organization' );
CALL objtrackerP_Audit_Define_Column(	1000,1062,'e','DepartmentID', 'Department', 'Person belongs to department' );
CALL objtrackerP_Audit_Define_Column(	1000,1091,'e','Track_Changed', 'Last changed', 'Time that this row was last changed' );
CALL objtrackerP_Audit_Define_Column(	1000,1092,'e','Track_Userid', 'Changed by', 'User name of the last change' );
