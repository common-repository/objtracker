DROP PROCEDURE IF EXISTS objtrackerP_TargetObjUpdate;
DELIMITER $$
/*
	Call objtrackerP_TargetObjUpdate( 5)
*/
CREATE PROCEDURE objtrackerP_TargetObjUpdate (
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_ID				INT
	,C_FiscalYear		INT			
	,C_Target			varchar(16)	
	,C_Target1	 		varchar(16)
	,C_Target2			varchar(16)
)
BEGIN
	SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

	UPDATE objtrackerT_Target SET
		Target			= C_Target
		,Target1		= C_Target1
		,Target2		= C_Target2
		,Track_Changed	= Current_Timestamp
		,Track_Userid	= @UserName
	WHERE OrganizationID = C_CallerOrg AND objtrackerT_Target.ID = C_ID AND objtrackerT_Target.FiscalYear = C_FiscalYear;

	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
END
$$
