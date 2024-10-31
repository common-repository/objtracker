DROP TRIGGER IF EXISTS objtrackerX_Measurement_Insert;
DROP TRIGGER IF EXISTS objtrackerX_Measurement_Update;
DROP TRIGGER IF EXISTS objtrackerX_Measurement_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_Measurement_Insert AFTER INSERT ON objtrackerT_Measurement
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
  SELECT substr(Title,1,64) INTO @this FROM objtrackerT_Objective WHERE ID = New.ObjectiveID;
  
	CALL objtrackerP_Audit_Add_auditindex(New.OrganizationID,@myGuid,@now, New.Track_Userid	,2000,1,0, 'A' ,@this ); 
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2005, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2001, 'A', 'A',New.ID );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2042, 'A', 'A',New.ObjectiveID );
	CALL objtrackerP_Audit_datetime(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2043, 'A', 'A',New.PeriodStarting );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2051, 'A', 'A', '',New.Measurement );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2052, 'A', 'A', '',New.Notes );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Measurement_Update AFTER UPDATE ON objtrackerT_Measurement
FOR EACH ROW BEGIN
  SET @now := Now();	
	SET @myGuid := UUID();
  SELECT substr(Title,1,64) INTO @this FROM objtrackerT_Objective WHERE ID = Old.ObjectiveID;
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, New.Track_Userid	,2000,1,0, 'U' ,@this );	
	if New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2001, 'U', 'U',Old.ID, New.ID );
	END IF;
	if New.ObjectiveID <> Old.ObjectiveID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2042, 'U', 'U',Old.ObjectiveID, New.ObjectiveID );
	END IF;
	if New.PeriodStarting <> Old.PeriodStarting THEN
		CALL objtrackerP_Audit_datetime2( Old.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2043, 'U', 'U',Old.PeriodStarting, New.PeriodStarting );
	END IF;
	if New.Measurement <> Old.Measurement THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2051, 'U', 'U',Old.Measurement, New.Measurement );
	END IF;
	if New.Notes <> Old.Notes THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 2000, 1, 2052, 'U', 'U',Old.Notes, New.Notes );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Measurement_Delete AFTER DELETE ON objtrackerT_Measurement
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
  SELECT substr(Title,1,64) INTO @this FROM objtrackerT_Objective WHERE ID = Old.ObjectiveID;
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 2000, 1,0, 'D' ,@this );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 2000, 1, 2005,'D','D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 2000, 1, 2001, 'D', 'D',Old.ID );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 2000, 1, 2042, 'D', 'D',Old.ObjectiveID );
	CALL objtrackerP_Audit_datetime(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 2000, 1, 2043, 'D', 'D',Old.PeriodStarting  );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 2000, 1, 2051, 'D', 'D',Old.Measurement,'' );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 2000, 1, 2052, 'D', 'D',Old.Notes,'' ); -- old,new
END
$$
CALL objtrackerP_Audit_Define_table(	2000,'objtrackerT_Measurement', 'Measurements', 'This table lists an objective''s measurements.' );
CALL objtrackerP_Audit_Define_Column(	2000,2005,'e','OrganizationID', 'OrganizationID',	'The organization ID');
CALL objtrackerP_Audit_Define_Column(	2000,2001,'e','ID', 'ID', 'The unique row identifier of the Measurement table for an organization' );
CALL objtrackerP_Audit_Define_Column(	2000,2042,'e','ObjectiveID', 'Objective',	'References the objective for this measurement'  );
CALL objtrackerP_Audit_Define_Column(	2000,2043,'e','PeriodStarting', 'Period Starting', 'Date that period begins.' );
CALL objtrackerP_Audit_Define_Column(	2000,2051,'e','Measurement', 'Measurement', 'The value measured' );
CALL objtrackerP_Audit_Define_Column(	2000,2052,'e','Notes', 'Notes', 'Additional detail describing this measurement' );
CALL objtrackerP_Audit_Define_Column(	2000,2091,'e','Track_Changed', 'Last changed', 'Time that this row was last changed' );
CALL objtrackerP_Audit_Define_Column(	2000,2092,'e','Track_Userid', 'Changed by', 'User name of the last change' );
