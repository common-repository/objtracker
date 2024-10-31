DROP PROCEDURE IF EXISTS objtrackerP_PersonFactsGet;
DELIMITER $$
/*
 CALL objtrackerP_PersonFactsGet(1,0,'JustDan', 'JustDan', 'Yes' );
 select * from objtrackerT_Person
 delete from objtrackerT_Person where id = 2
	SELECT ID FROM objtrackerT_Person WHERE UserName = 'Sam';
*/
CREATE PROCEDURE objtrackerP_PersonFactsGet (
	C_CallerOrg				INT 
	,C_CallerUser			INT 
	,C_UserName 			VARCHAR(32)
	,C_FullName 			VARCHAR(32)
	,C_Admin 				VARCHAR(3)
)
BEGIN
  IF C_Admin = 'Yes' THEN
  	IF NOT EXISTS (SELECT * FROM objtrackerT_Organization WHERE ID = C_CallerOrg) THEN
		CALL objtrackerP_OrgNInit(C_CallerOrg,C_UserName);
  	END IF;
  	IF NOT EXISTS (SELECT * FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND UserName = C_UserName) THEN
		SELECT MIN(ID) INTO @DeptID FROM objtrackerT_Department WHERE OrganizationID = C_CallerOrg; 
		INSERT INTO objtrackerT_Person (
			ID
			,OrganizationID
			,Admin
			,Active
			,FullName
			,UserName
			,Password
			,DepartmentID
			,UiSettings
			,Track_Changed
			,Track_Userid
		)
		SELECT
			IFNULL( (SELECT MAX(ID)+1 from objtrackerT_Person), 1)
			,C_CallerOrg
			,1
			,1
			,C_FullName
			,C_UserName
			,'not used'
			,@DeptID
			,'SSSS............'
			,now()
			,C_UserName;

  	END IF;
  END IF;
  
  SET @FiscalYear := objtrackerF_FiscalYear(C_CallerOrg);
  SELECT Title INTO @FiscalYearTitle FROM objtrackerT_FiscalYear WHERE OrganizationID = C_CallerOrg AND ID = @FiscalYear ;
  IF @FiscalYearTitle is null THEN
   SET @FiscalYearTitle = Cast(@FiscalYear AS CHAR(4));
  END IF;
  SELECT 
	objtrackerT_Person.ID AS C_ID
	, C_CallerOrg AS C_OrganizationID
	, objtrackerT_Person.FullName AS C_FullName
	, Case WHEN objtrackerT_Person.Admin = 1 THEN 'Yes' ELSE 'No' END AS C_IsAdmin
	, 'No' AS C_IsViewer
	, @FiscalYear AS C_FiscalYear1
	, @FiscalYearTitle AS C_FiscalYearTitle
	, objtrackerT_Department.Title AS C_Department 
	, objtrackerT_Organization.Title AS C_Organization
	, objtrackerT_Organization.FirstMonth AS C_FirstMonth
	, objtrackerT_Organization.UploadFsPath AS C_UploadFsPath  
	, objtrackerT_Organization.Trailer AS C_Trailer
	, objtrackerT_Person.UiSettings AS C_UiSettings 
	, objtrackerT_Person.Track_Changed AS C_Track_Changed 
	, objtrackerT_Person.Track_Userid AS C_Track_Userid
  FROM  objtrackerT_Person 
  JOIN objtrackerT_Organization 	ON objtrackerT_Person.OrganizationID = objtrackerT_Organization.Id 
  JOIN objtrackerT_Department		ON objtrackerT_Person.DepartmentID  	= objtrackerT_Department.Id 
  					   AND objtrackerT_Person.OrganizationID 	= objtrackerT_Department.OrganizationID 
  WHERE objtrackerT_Person.UserName = C_UserName AND objtrackerT_Person.Active = 1;
END
$$
