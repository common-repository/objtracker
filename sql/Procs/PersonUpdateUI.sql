DROP PROCEDURE IF EXISTS objtrackerP_PersonUpdateUI;
DELIMITER $$
/*
 select ID,UiSettings from objtrackerT_Person
 call objtrackerP_PersonUpdateUi(1,'HSSS...........');
 call objtrackerP_PersonUpdateUi(1,'SSSS...........');
*/
CREATE PROCEDURE objtrackerP_PersonUpdateUI (
	C_CallerOrg		INT
	,C_CallerUser	INT
	,C_UiSettings	VARCHAR (16)
)
BEGIN
  	UPDATE objtrackerT_Person SET
  		UiSettings = C_UiSettings
	WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;

	SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
END
$$
