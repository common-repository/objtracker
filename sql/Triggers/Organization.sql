DROP TRIGGER IF EXISTS objtrackerX_Organization_Insert;
DROP TRIGGER IF EXISTS objtrackerX_Organization_Update;
DROP TRIGGER IF EXISTS objtrackerX_Organization_Delete;
DELIMITER $$
CREATE TRIGGER objtrackerX_Organization_Insert AFTER INSERT ON objtrackerT_Organization
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(New.ID,@myGuid,@now, New.Track_Userid	,900,1,0, 'A' ,New.Title ); 
	CALL objtrackerP_Audit_char(		New.ID,@myGuid,@now, New.Track_Userid, 900, 1, 901, 'A', 'A', '',New.ID );
	CALL objtrackerP_Audit_bitchar(	New.ID,@myGuid,@now, New.Track_Userid, 900, 1, 902, 'A', 'A', New.Active );
	CALL objtrackerP_Audit_varchar(	New.ID,@myGuid,@now, New.Track_Userid, 900, 1, 951, 'A', 'A', '',New.Title );
	CALL objtrackerP_Audit_varchar(	New.ID,@myGuid,@now, New.Track_Userid, 900, 1, 952, 'A', 'A', '',New.ShortTitle );
	CALL objtrackerP_Audit_int(		New.ID,@myGuid,@now, New.Track_Userid, 900, 1, 953, 'A', 'A', New.FirstMonth );
	CALL objtrackerP_Audit_varchar(	New.ID,@myGuid,@now, New.Track_Userid, 900, 1, 954, 'A', 'A', '',New.UploadFsPath );
	CALL objtrackerP_Audit_varchar(	New.ID,@myGuid,@now, New.Track_Userid, 900, 1, 955, 'A', 'A', '',New.Trailer );
	CALL objtrackerP_Audit_varchar(	New.ID,@myGuid,@now, New.Track_Userid, 900, 1, 956, 'A', 'A', '','new default' );
	CALL objtrackerP_Audit_bitchar(	New.ID,@myGuid,@now, New.Track_Userid, 900, 1, 903, 'A', 'A', New.ChangePassword );
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Organization_Update AFTER UPDATE ON objtrackerT_Organization
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.ID,@myGuid,@now, New.Track_Userid	,900,1,0, 'U' ,Old.Title );
	if New.ID <> Old.ID THEN
		CALL objtrackerP_Audit_char(		Old.ID,@myGuid,@now, New.Track_Userid, 900, 1, 901, 'U', 'U',Old.ID, New.ID );
	END IF;
	if New.Active <> Old.Active THEN
		CALL objtrackerP_Audit_bit2char(	Old.ID,@myGuid,@now, New.Track_Userid, 900, 1, 902, 'U', 'U',Old.Active, New.Active );
	END IF;
	if New.Title <> Old.Title THEN
		CALL objtrackerP_Audit_varchar(	Old.ID,@myGuid,@now, New.Track_Userid, 900, 1, 951, 'U', 'U',Old.Title, New.Title );
	END IF;
	if New.ShortTitle <> Old.ShortTitle THEN
		CALL objtrackerP_Audit_varchar(	Old.ID,@myGuid,@now, New.Track_Userid, 900, 1, 952, 'U', 'U',Old.ShortTitle, New.ShortTitle );
	END IF;
	if New.FirstMonth <> Old.FirstMonth THEN
		CALL objtrackerP_Audit_int2(		Old.ID,@myGuid,@now, New.Track_Userid, 900, 1, 953, 'U', 'U',Old.FirstMonth, New.FirstMonth );
	END IF;
	if New.UploadFsPath <> Old.UploadFsPath THEN
		CALL objtrackerP_Audit_varchar(	Old.ID,@myGuid,@now, New.Track_Userid, 900, 1, 954, 'U', 'U',Old.UploadFsPath, New.UploadFsPath );
	END IF;
	if New.Trailer <> Old.Trailer THEN
		CALL objtrackerP_Audit_varchar(	Old.ID,@myGuid,@now, New.Track_Userid, 900, 1, 955, 'U', 'U',Old.Trailer, New.Trailer );
	END IF;
	if New.ChangePassword <> Old.ChangePassword THEN
		CALL objtrackerP_Audit_bit2char(	Old.ID,@myGuid,@now, New.Track_Userid, 900, 1, 903, 'U', 'U',Old.ChangePassword, New.ChangePassword );
	END IF;
	if New.DefaultPassword <> Old.DefaultPassword THEN
		CALL objtrackerP_Audit_varchar(	Old.ID,@myGuid,@now, New.Track_Userid, 900, 1, 956, 'U', 'U','old value', 'new value' );
	END IF;
END
$$
DELIMITER $$
CREATE TRIGGER objtrackerX_Organization_Delete AFTER DELETE ON objtrackerT_Organization
FOR EACH ROW BEGIN
	SET @now := Now();	
	SET @myGuid := UUID();
	CALL objtrackerP_Audit_Add_auditindex(Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1,0, 'D' ,Old.Title );
	CALL objtrackerP_Audit_Delete_char(	Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1, 901, 'D', 'D',Old.ID );
	CALL objtrackerP_Audit_Delete_bitchar(Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1, 902, 'D', 'D',Old.Active );
	CALL objtrackerP_Audit_varchar(		Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1, 951, 'D', 'D',Old.Title,'' );
	CALL objtrackerP_Audit_varchar(		Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1, 952, 'D', 'D',Old.ShortTitle,'' );
	CALL objtrackerP_Audit_Delete_int(	Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1, 953, 'D', 'D',Old.FirstMonth );
	CALL objtrackerP_Audit_varchar(		Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1, 954, 'D', 'D',Old.UploadFsPath,'' );
	CALL objtrackerP_Audit_varchar(		Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1, 955, 'D', 'D',Old.Trailer,'' );
	CALL objtrackerP_Audit_varchar(		Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1, 956, 'D', 'D','old value','' );
	CALL objtrackerP_Audit_Delete_bitchar(Old.ID,@myGuid,@now, Old.Track_Userid, 900, 1, 903, 'D', 'D',Old.ChangePassword );
END
$$
CALL objtrackerP_Audit_Define_table(	900,'objtrackerT_Organization', 'Organization', 'This table lists the choices for organizations and provides adjustable attributes.' );
CALL objtrackerP_Audit_Define_Column( 900,901,'e','ID', 'ID', 'The unique row identifier of the Organization table' );
CALL objtrackerP_Audit_Define_Column( 900,902,'e','Active', 'Active', 'Active=Yes rows are selectable for common usage, Active=No required for maintaining history' );
CALL objtrackerP_Audit_Define_Column( 900,903,'e','ChangePassword', 'Change Password', 'ChangePassword=Yes requires users to change password from the default' );
CALL objtrackerP_Audit_Define_Column( 900,951,'e','Title', 'Title', 'Common name of this Organization' );
CALL objtrackerP_Audit_Define_Column( 900,952,'e','ShortTitle', 'ShortTitle', 'Short name of this Organization' );
CALL objtrackerP_Audit_Define_Column( 900,953,'e','FirstMonth', 'Month of Fiscal Year', 'Month that fiscal year starts' );
CALL objtrackerP_Audit_Define_Column( 900,954,'e','UploadFsPath', 'Upload Filesystem Path', 'Pc path to uploaded files' );
CALL objtrackerP_Audit_Define_Column( 900,955,'e','Trailer', 'Trailer', 'Value is placed at on the left side of the bottom of all pages. ' );
CALL objtrackerP_Audit_Define_Column( 900,956,'e','DefaultPassword', 'Default Password', 'Password for new user IDs or on password reset. ' );
CALL objtrackerP_Audit_Define_Column( 900,991,'e','Track_Changed', 'Last changed', 'Time that this row was last changed' );
CALL objtrackerP_Audit_Define_Column( 900,992,'e','Track_Userid', 'Changed by', 'User name of the last change' );
