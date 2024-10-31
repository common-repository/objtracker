DROP TRIGGER IF EXISTS objtrackerX_Target_Insert;
DROP TRIGGER IF EXISTS objtrackerX_Target_Update;
DROP TRIGGER IF EXISTS objtrackerX_Target_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_Target_Insert AFTER INSERT ON objtrackerT_Target
FOR EACH ROW BEGIN
  SELECT substr(Title,1,64) INTO @Title FROM objtrackerT_Objective WHERE ID = New.ID;
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(New.OrganizationID,@myGuid,@now, New.Track_Userid	,4000,1,0, 'A' ,@Title  );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4005, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4001, 'A', 'A',New.ID );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4002, 'A', 'A',New.FiscalYear );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4051, 'A', 'A', '',New.Target );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4052, 'A', 'A', '',New.Target1 );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4053, 'A', 'A', '',New.Target2 );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Target_Update AFTER UPDATE ON objtrackerT_Target
FOR EACH ROW BEGIN
  SELECT substr(Title,1,64) INTO @Title FROM objtrackerT_Objective WHERE ID = Old.ID;
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, New.Track_Userid	,4000,1,0, 'U' ,@Title  );
	if New.FiscalYear <> Old.FiscalYear THEN
		CALL objtrackerP_Audit_int2(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4002, 'U', 'U',Old.FiscalYear, New.FiscalYear );
	END IF;
	if New.Target <> Old.Target THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4051, 'U', 'U',Old.Target, New.Target );
	END IF;
	if New.Target1 <> Old.Target1 THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4052, 'U', 'U',Old.Target1, New.Target1 );
	END IF;
	if New.Target2 <> Old.Target2 THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 4000, 1, 4053, 'U', 'U',Old.Target2, New.Target2 );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Target_Delete AFTER DELETE ON objtrackerT_Target
FOR EACH ROW BEGIN
  SELECT substr(Title,1,64) INTO @Title FROM objtrackerT_Objective WHERE ID = Old.ID;
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 4000, 1,0, 'D' ,@Title );	
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 4000, 1, 4005,'D','D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 4000, 1, 4001, 'D', 'D',Old.ID );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 4000, 1, 4002, 'D', 'D',Old.FiscalYear );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 4000, 1, 4051, 'D', 'D',Old.Target,'' ); 
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 4000, 1, 4052, 'D', 'D',Old.Target1,'' );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 4000, 1, 4053, 'D', 'D',Old.Target2,'' );
END
$$
CALL objtrackerP_Audit_Define_table(	4000,'objtrackerT_Target', 'Targets', 'This table lists the targets for each objective.' );
CALL objtrackerP_Audit_Define_Column(	4000,4005,'e','OrganizationID', 'OrganizationID',	'The organization ID');
CALL objtrackerP_Audit_Define_Column(	4000,4001,'e','ID', 'ID', 'The unique row identifier of the Target table for an organization' );
CALL objtrackerP_Audit_Define_Column(	4000,4002,'e','FiscalYear', 'FiscalYear', 'FiscalYear for targes' );
CALL objtrackerP_Audit_Define_Column(	4000,4051,'e','Target', 'Target', 'Value of objectives goal' );
CALL objtrackerP_Audit_Define_Column(	4000,4052,'e','Target1', 'Target1', 'Value of objectives near target(Green)' );
CALL objtrackerP_Audit_Define_Column(	4000,4053,'e','Target2', 'Target2', 'Value of objectives far target(Yellow)' );
CALL objtrackerP_Audit_Define_Column(	4000,4091,'e','Track_Changed', 'Last changed', 'Time that this row was last changed' );
CALL objtrackerP_Audit_Define_Column(	4000,4092,'e','Track_Userid', 'Changed by', 'User name of the last change' );
