DROP PROCEDURE IF EXISTS objtrackerP_OrgNInit;
DELIMITER $$
/*
	call objtrackerP_OrgNInit(1,'me');
	call objtrackerP_OrgNInit(-1,'me');
*/
CREATE PROCEDURE objtrackerP_OrgNInit (
	C_OrganizationID		INT  
	,C_UserName 			VARCHAR(32)
)
BEGIN
IF C_OrganizationID < 0 THEN
	SELECT * FROM objtrackerT_Person ;
	SELECT * FROM objtrackerT_Department  ;
	SELECT * FROM objtrackerT_FiscalYear  ;
	SELECT * FROM objtrackerT_ObjectiveType  ;
	SELECT * FROM objtrackerT_Frequency ;
	SELECT * FROM objtrackerT_MetricType ;
	SELECT * FROM objtrackerT_Organization ;
ELSE
	-- Organization
	INSERT INTO objtrackerT_Organization (
		ID, Title, ShortTitle, FirstMonth, UploadFsPath,Trailer,DefaultPassword, Track_Userid
	) 
	SELECT 
		C_OrganizationID
		, CONCAT('New Organization - ', CAST(C_OrganizationID AS CHAR(8)) )
		, CONCAT('NO', CAST(C_OrganizationID AS CHAR(8)) )
		, FirstMonth, UploadFsPath,Trailer,DefaultPassword, C_UserName
	FROM objtrackerT_Organization WHERE ID = 0	
	;

	-- Objective Type
	INSERT INTO objtrackerT_ObjectiveType  (
		OrganizationID,ID,Active,Title,Title2,Description,Track_Userid
	) 
	SELECT 
		C_OrganizationID,ID,Active,Title,Title2,Description,C_UserName
	FROM objtrackerT_ObjectiveType WHERE OrganizationID = 0;

	-- Frequency
	INSERT INTO objtrackerT_Frequency (
		OrganizationID,ID, Count_Months, WeeksToAlert, Title, Description,Track_Userid
	) 
	SELECT 
		C_OrganizationID,ID, Count_Months, WeeksToAlert, Title, Description,C_UserName
	FROM objtrackerT_Frequency WHERE OrganizationID = 0;

	-- Metric Type
	INSERT INTO objtrackerT_MetricType (
		OrganizationID,ID,Title,Description,Track_Userid
	) SELECT 
		C_OrganizationID,ID,Title,Description,C_UserName
	FROM objtrackerT_MetricType WHERE OrganizationID = 0;


	-- Department
	INSERT INTO objtrackerT_Department  (
		OrganizationID,ID,ParentID,Title,Title2,Track_Userid
	) VALUES ( 
		C_OrganizationID,1,0,'Replace me at setup','Replace',C_UserName
	) ;

END IF;
END
$$
