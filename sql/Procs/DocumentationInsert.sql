DROP PROCEDURE IF EXISTS objtrackerP_DocumentationInsert;
DELIMITER $$
/*
	call objtrackerP_DocumentationInsert( 1,1,1, '2012-10-01','fn','desc','mt','me')
	call objtrackerP_DocumentationDelete( 1,1,1 )
  SELECT * from objtrackerT_Documentation
*/
CREATE PROCEDURE objtrackerP_DocumentationInsert (
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_ObjectiveID		INT
	,C_PeriodStarting	DateTime
	,C_FileName			VARCHAR (64)
	,C_Description		VARCHAR (64)
	,C_MimeType			VARCHAR (64)
)
BEGIN
	Declare C_id INT;
	IF C_Description is NULL THEN
		SET C_Description := '';
	END IF;
	SELECT MAX(ID)+1 INTO C_ID FROM objtrackerT_Documentation WHERE OrganizationID = C_CallerOrg;
	IF C_ID IS NULL THEN
		SET C_id := 1;
	END IF;

	SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;
	
	INSERT INTO objtrackerT_Documentation (
		ID
		,OrganizationID
		,ObjectiveID,PeriodStarting
		,Description,FileName,MimeType
		,Active
		,Track_Added
		,Track_Userid
	) VALUES(
		C_id
		,C_CallerOrg
		,C_ObjectiveID,C_PeriodStarting
		,C_Description,C_FileName,C_MimeType
		,1  -- Active
		,Now()
		,@UserName
	) ;

	SELECT C_ID;
END
$$
