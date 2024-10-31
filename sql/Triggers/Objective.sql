DROP TRIGGER IF EXISTS objtrackerX_Objective_Insert;
DROP TRIGGER IF EXISTS objtrackerX_Objective_Update;
DROP TRIGGER IF EXISTS objtrackerX_Objective_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_Objective_Insert AFTER INSERT ON objtrackerT_Objective
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000,1,0, 'A' ,substr(New.Title,1,64) ); 
	CALL objtrackerP_Audit_int(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3005, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_int(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3001, 'A', 'A',New.ID );
	CALL objtrackerP_Audit_int(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3020, 'A', 'A',New.FiscalYear1 );
	CALL objtrackerP_Audit_int(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3021, 'A', 'A',New.FiscalYear2 );
	CALL objtrackerP_Audit_int(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3023, 'A', 'A',New.OwnerID );
	CALL objtrackerP_Audit_bitchar(New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3025, 'A', 'A',New.IsPublic );
	CALL objtrackerP_Audit_char(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3026, 'A', 'A', '',New.TypeID );
	CALL objtrackerP_Audit_char(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3027, 'A', 'A', '',New.FrequencyID );
	CALL objtrackerP_Audit_char(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3028, 'A', 'A', '',New.MetricTypeID );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3051, 'A', 'A','',New.Title );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3052, 'A', 'A','',New.Description );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3053, 'A', 'A','',New.Source );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Objective_Update AFTER UPDATE ON objtrackerT_Objective
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000,1,0, 'U' ,substr(Old.Title,1,64) 	);	
	if New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3001, 'U', 'U',Old.ID, New.ID );
	END IF;
	if New.FiscalYear1 <> Old.FiscalYear1 THEN
		CALL objtrackerP_Audit_int2(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3020, 'U', 'U',Old.FiscalYear1, New.FiscalYear1 );
	END IF;
	if New.FiscalYear2 <> Old.FiscalYear2 THEN
		CALL objtrackerP_Audit_int2(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3021, 'U', 'U',Old.FiscalYear2, New.FiscalYear2 );
	END IF;
	if New.OwnerID <> Old.OwnerID THEN
		CALL objtrackerP_Audit_int2(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3023, 'U', 'U',Old.OwnerID, New.OwnerID );
	END IF;
	if New.IsPublic <> Old.IsPublic THEN
		CALL objtrackerP_Audit_bit2(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3025, 'U', 'U',Old.IsPublic, New.IsPublic );
	END IF;
	if New.TypeID <> Old.TypeID THEN
		CALL objtrackerP_Audit_char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3026, 'U', 'U',Old.TypeID, New.TypeID );
	END IF;
	if New.FrequencyID <> Old.FrequencyID THEN
		CALL objtrackerP_Audit_char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3027, 'U', 'U',Old.FrequencyID, New.FrequencyID );
	END IF;
	if New.MetricTypeID <> Old.MetricTypeID THEN
		CALL objtrackerP_Audit_char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3028, 'U', 'U',Old.MetricTypeID, New.MetricTypeID );
	END IF;
	if New.Title <> Old.Title THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3051, 'U', 'U',Old.Title, New.Title );
	END IF;
	if New.Description <> Old.Description THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3052, 'U', 'U',Old.Description, New.Description );
	END IF;
	if New.Source <> Old.Source THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 3000, 1, 3053, 'U', 'U',Old.Source, New.Source );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Objective_Delete AFTER DELETE ON objtrackerT_Objective
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1,0, 'D' ,substr(Old.Title,1,64)  );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3005,'D','D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3001, 'D', 'D',Old.ID );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3020, 'D', 'D',Old.FiscalYear1 );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3021, 'D', 'D',Old.FiscalYear2 );
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3023, 'D', 'D',Old.OwnerID );
	CALL objtrackerP_Audit_Delete_bit(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3025, 'D', 'D',Old.IsPublic );
	CALL objtrackerP_Audit_Delete_char(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3026, 'D', 'D',Old.TypeID );
	CALL objtrackerP_Audit_Delete_char(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3027, 'D', 'D',Old.FrequencyID );
	CALL objtrackerP_Audit_Delete_char(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3028, 'D', 'D',Old.MetricTypeID );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3051, 'D', 'D',Old.Title,'' );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3052, 'D', 'D',Old.Description,'' );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 3000, 1, 3053, 'D', 'D',Old.Source,'' );
END
$$
CALL objtrackerP_Audit_Define_table(	
	3000,'objtrackerT_Objective', 'Objective', 'Collection of objectives.'
 );
CALL objtrackerP_Audit_Define_Column(
	3000,3005,'e','OrganizationID', 'OrganizationID',	'The organization ID');
CALL objtrackerP_Audit_Define_Column( 
	3000,3001,'e','ID', 'ID', 'The unique row identifier of the Objective table for an organization'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3020,'e','FiscalYear1', 'FiscalYear1', 'First year of starting fiscal year'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3021,'e','FiscalYear2', 'FiscalYear2', 'First year of ending fiscal year'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3023,'e','OwnerID', 'OwnerID', 'Owning person'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3025,'e','IsPublic', 'IsPublic', 'True = Public, False = Private'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3026,'e','TypeID', 'TypeID', 'Customer, Financial, Business Process, Employee Growth '
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3027,'e','FrequencyID', 'FrequencyID', 'How often measurement is taken'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3028,'e','MetricTypeID', 'MetricTypeID', 'Type of number, such as percent, integer...'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3051,'e','Title', 'Title', 'Common name of this objective'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3052,'e','Description', 'Description', 'Additional detail describing this objective'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3053,'e','Source', 'Source', 'Where measurement is derived from'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3091,'e','Track_Changed', 'Last changed', 'Time that this row was last changed'
 );
CALL objtrackerP_Audit_Define_Column(	
	3000,3092,'e','Track_Userid', 'Changed by', 'User name of the last change'
 );
