DROP TRIGGER IF EXISTS objtrackerX_MetricType_Insert;
DROP TRIGGER IF EXISTS objtrackerX_MetricType_Update;
DROP TRIGGER IF EXISTS objtrackerX_MetricType_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_MetricType_Insert AFTER INSERT ON objtrackerT_MetricType
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(New.OrganizationID,@myGuid,@now, New.Track_Userid	,600,1,0, 'A' ,New.Title  );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 600, 1, 605, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_char(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 600, 1, 601, 'A', 'A', '',New.ID );
	CALL objtrackerP_Audit_bitchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 600, 1, 602, 'A', 'A',New.Active );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 600, 1, 651, 'A', 'A', '',New.Title );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 600, 1, 652, 'A', 'A', '',New.Description );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_MetricType_Update AFTER UPDATE ON objtrackerT_MetricType
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, New.Track_Userid	,600,1,0, 'U' ,Old.Title 	 );	
	if New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_char(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 600, 1, 601, 'U', 'U',Old.ID, New.ID );
	END IF;
	if New.Active <> Old.Active THEN
		CALL objtrackerP_Audit_bit2char(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 600, 1, 602, 'U', 'U',Old.Active, New.Active );
	END IF;
	if New.Title <> Old.Title THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 600, 1, 651, 'U', 'U',Old.Title, New.Title );
	END IF;
	if New.Description <> Old.Description THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 600, 1, 652, 'U', 'U',Old.Description, New.Description );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_MetricType_Delete AFTER DELETE ON objtrackerT_MetricType
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 600, 1,0, 'D' ,Old.Title  );		
	CALL objtrackerP_Audit_Delete_int(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 600, 1, 605,'D','D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_char(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 600, 1, 601, 'D', 'D',Old.ID );
	CALL objtrackerP_Audit_Delete_bitchar(Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 600, 1, 602, 'D', 'D',Old.Active );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 600, 1, 651, 'D', 'D',Old.Title,'' ); 
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 600, 1, 652, 'D', 'D',Old.Description,'' );
END
$$
CALL objtrackerP_Audit_Define_table(	600,'objtrackerT_MetricType', 'Metric type', 'This table lists the choices for metric type of objective''s measurements.' );
CALL objtrackerP_Audit_Define_Column(	600,605,'e','OrganizationID', 'OrganizationID',	'The organization ID');
CALL objtrackerP_Audit_Define_Column(	600,601,'e','ID', 'ID', 'The unique row identifier of the MetricType table for an organization.' );
CALL objtrackerP_Audit_Define_Column(	600,602,'e','Active', 'Active', 'Active=Yes rows are selectable for common usage, Active=No required for maintaining history' );
CALL objtrackerP_Audit_Define_Column(	600,651,'e','Title', 'Title', 'Common name of this metric type' );
CALL objtrackerP_Audit_Define_Column(	600,652,'e','Description', 'Description', 'Additional detail describing this metric type' );
CALL objtrackerP_Audit_Define_Column(	600,691,'e','Track_Changed', 'Last changed', 'Time that this row was last changed' );
CALL objtrackerP_Audit_Define_Column(	600,692,'e','Track_Userid', 'Changed by', 'User name of the last change' );
