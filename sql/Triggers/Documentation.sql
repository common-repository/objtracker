DROP TRIGGER IF EXISTS objtrackerX_Document_Insert;
DROP TRIGGER IF EXISTS objtrackerX_Document_Update;
DROP TRIGGER IF EXISTS objtrackerX_Document_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_Document_Insert AFTER INSERT ON objtrackerT_Documentation 
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
  SELECT substr(Title,1,64) INTO @this FROM objtrackerT_Objective WHERE ID = New.ObjectiveID AND OrganizationID = New.OrganizationID;
  CALL objtrackerP_Audit_Add_auditindex(	New.OrganizationID,@myGuid,@now, New.Track_Userid	,5000,1,0, 'A' ,@this ); 
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5001, 'A', 'A',New.ID );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5005, 'A', 'A',New.OrganizationID );
	CALL objtrackerP_Audit_int(		New.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5042, 'A', 'A',New.ObjectiveID );
	CALL objtrackerP_Audit_datetime(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5043, 'A', 'A',New.PeriodStarting );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5051, 'A', 'A', '',New.FileName );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5052, 'A', 'A', '',New.Description );
	CALL objtrackerP_Audit_varchar(	New.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5053, 'A', 'A', '',New.MimeType );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Document_Update AFTER UPDATE ON objtrackerT_Documentation 
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
  SELECT substr(Title,1,64) INTO @this FROM objtrackerT_Objective WHERE ID = New.ObjectiveID AND OrganizationID = Old.OrganizationID;
CALL objtrackerP_Audit_Add_auditindex(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 0, 'U' ,@this  );		
	if New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5001, 'U', 'U',Old.ID, New.ID );
	END IF;
	if New.ObjectiveID <> Old.ObjectiveID THEN
		CALL objtrackerP_Audit_int2(		Old.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5042, 'U', 'U',Old.ObjectiveID, New.ObjectiveID );
	END IF;
	if New.PeriodStarting <> Old.PeriodStarting THEN
		CALL objtrackerP_Audit_datetime2( Old.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5043, 'U', 'U',Old.PeriodStarting, New.PeriodStarting );
	END IF;
	if New.FileName<> Old.FileName THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5051, 'U', 'U',Old.FileName, New.FileName );
	END IF;
	if New.Description <> Old.Description THEN
		CALL objtrackerP_Audit_varchar(	Old.OrganizationID,@myGuid,@now, New.Track_Userid, 5000, 1, 5052, 'U', 'U',Old.Description, New.Description );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Document_Delete AFTER DELETE ON objtrackerT_Documentation 
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
  SELECT substr(Title,1,64) INTO @this FROM objtrackerT_Objective WHERE ID = Old.ObjectiveID AND OrganizationID = Old.OrganizationID;
	CALL objtrackerP_Audit_Add_auditindex(	Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 5000, 1,0, 'D' ,@this  );
	CALL objtrackerP_Audit_Delete_int(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 5000, 1, 5001, 'D', 'D',Old.ID );
	CALL objtrackerP_Audit_Delete_int(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 5000, 1, 5005, 'D', 'D',Old.OrganizationID );
	CALL objtrackerP_Audit_Delete_int(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 5000, 1, 5042, 'D', 'D',Old.ObjectiveID );
	CALL objtrackerP_Audit_datetime(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 5000, 1, 5043, 'A', 'A',Old.PeriodStarting  );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 5000, 1, 5051, 'D', 'D',Old.FileName,'' );
	CALL objtrackerP_Audit_varchar(		Old.OrganizationID,@myGuid,@now, Old.Track_Userid, 5000, 1, 5052, 'D', 'D',Old.Description,''  ); -- old,new
END
$$
CALL objtrackerP_Audit_Define_table(	5000,'objtrackerT_Documentation', 'Documents', 'This table lists an objective''s uploaded documents.' );
CALL objtrackerP_Audit_Define_Column(	5000,5001,'e','ID', 'ID', 'The unique row identifier of the Document table for an organization' );
CALL objtrackerP_Audit_Define_Column(	5000,5005,'e','OrganizationID', 'OrganizationID', 'The organization' );
CALL objtrackerP_Audit_Define_Column(	5000,5042,'e','ObjectiveID', 'Objective',	'References the objective for this measurement' );
CALL objtrackerP_Audit_Define_Column(	5000,5043,'e','PeriodStarting', 'Period Starting', 'Date that period begins.' );
CALL objtrackerP_Audit_Define_Column(	5000,5051,'e','Filename', 'FileName', 'The value measured' );
CALL objtrackerP_Audit_Define_Column(	5000,5052,'e','Description', 'Description', 'Additional detail describing this upload' );
CALL objtrackerP_Audit_Define_Column(	5000,5053,'e','MimeType', 'MimeType', 'Additional detail describing this upload' );
CALL objtrackerP_Audit_Define_Column(	5000,5091,'e','Track_Added', 'Last changed', 'Time that this row was last changed' );
CALL objtrackerP_Audit_Define_Column(	5000,5092,'e','Track_Userid', 'Changed by', 'User name of the last change' );
