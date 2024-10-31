CREATE TABLE objtrackerT_Organization (
	ID				INT				NOT NULL
		,PRIMARY KEY(ID),
	Active			BIT				NOT NULL DEFAULT 1,
	ChangePassword	BIT				NOT NULL DEFAULT 0,
	FirstMonth		INT				NOT NULL,
	Title			VARCHAR  (64)	NOT NULL,
	ShortTitle		VARCHAR  (16)	NOT NULL,
	UploadFsPath 	VARCHAR(150)	NOT NULL,
	Trailer	 		VARCHAR(48)	 	NOT NULL DEFAULT '',
	DefaultPassword	VARCHAR(16)	 	NOT NULL DEFAULT '',
	Track_Changed 	TIMESTAMP		NOT NULL DEFAULT now(),
	Track_Userid	VARCHAR (50)	NOT NULL DEFAULT 'Init'
) ;
CREATE TABLE objtrackerT_FiscalYear ( 
	OrganizationID INT		NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID),
	ID			    INT			  NOT NULL 
		,PRIMARY KEY(OrganizationID,ID),
  	Active		 	BIT			  NOT NULL DEFAULT 1,
	Title			VARCHAR(9)	  NOT NULL,
	Track_Changed   timestamp     NOT NULL DEFAULT now(),
	Track_Userid	VARCHAR (50)  NOT NULL DEFAULT 'Init'
);
CREATE TABLE objtrackerT_ObjectiveType ( 
	OrganizationID INT				NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID),
	ID				CHAR				NOT NULL
		,PRIMARY KEY(OrganizationID,ID),
	Active			BIT					NOT NULL DEFAULT 1,
	Title			VARCHAR  (64)		NOT NULL,
	Title2			VARCHAR  (16)		NOT NULL,
	Description		VARCHAR  (128)	NOT NULL,
	Track_Changed	TIMESTAMP			NOT NULL DEFAULT now(),
	Track_Userid	VARCHAR (50)		NOT NULL DEFAULT 'Init'
) ;
CREATE TABLE objtrackerT_Frequency (
	OrganizationID INT		NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID),
	ID			CHAR				NOT NULL
		,PRIMARY KEY(OrganizationID,ID),
	Active		BIT				NOT NULL DEFAULT 1,
	Count_Months	INT				NOT NULL,
	WeeksToAlert	INT				NOT NULL,
	Title			VARCHAR  (64)		NOT NULL,
	Description	VARCHAR  (128)	NOT NULL,
	Track_Changed TIMESTAMP			NOT NULL DEFAULT now(),
	Track_Userid	VARCHAR (50)		NOT NULL DEFAULT 'Init'
) ;
CREATE TABLE objtrackerT_MetricType (
	OrganizationID INT		NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID),
	ID			CHAR				NOT NULL
		,PRIMARY KEY(OrganizationID,ID),
	Active		BIT				NOT NULL DEFAULT 1,
	Title			VARCHAR  (64)		NOT NULL,
	Description	VARCHAR  (128)	NOT NULL,
	Track_Changed TIMESTAMP			NOT NULL DEFAULT now(),
	Track_Userid	VARCHAR (50)		NOT NULL DEFAULT 'Init'
) ;
CREATE TABLE objtrackerT_Department(
	OrganizationID INT		NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID),
	ID 				INT 			NOT NULL
		,PRIMARY KEY(OrganizationID,ID),
	ParentID		INT			NOT NULL,
	Active			BIT			NOT NULL DEFAULT 1,
	Title 			VARCHAR(64) 	NOT NULL,
	Title2 			VARCHAR(16) 	NOT NULL,
	Track_Changed TIMESTAMP		NOT NULL DEFAULT now(),
	Track_Userid	VARCHAR (50)	NOT NULL DEFAULT 'Init'
) ;
CREATE TABLE objtrackerT_Person (
	OrganizationID	INT		NOT NULL,
	FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID),
	ID				INT	NOT NULL,
		PRIMARY KEY(OrganizationID,ID),
	DepartmentID	INT				NOT NULL,
	Admin			BIT				NOT NULL NULL DEFAULT 0,
	Active			BIT				NOT NULL DEFAULT 1,
	ChangePassword	BIT			NOT NULL DEFAULT 1,
	UserName		VARCHAR  (64)		NULL,
	FullName		VARCHAR  (64)		NOT NULL,
	Password		VARCHAR  (64)		NOT NULL,
	UiSettings		VARCHAR  (16)		NOT NULL,
	Track_Changed 	TIMESTAMP			NOT NULL DEFAULT now(),
	Track_Userid	VARCHAR (50)		NOT NULL
) ;
CREATE TABLE objtrackerT_Objective (
	OrganizationID INT			NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID),
	ID				INT				NOT NULL,
		PRIMARY KEY(OrganizationID,ID),
	FiscalYear1		INT				NOT NULL,
	FiscalYear2		INT				NOT NULL,
	UnusedID1		INT				NOT NULL DEFAULT 0,
	OwnerID			INT				NOT NULL,
	IsPublic		BIT				NOT NULL,
	Source			VARCHAR  (100)	NOT NULL,
	TypeID			CHAR			NOT NULL,
	FrequencyID		CHAR			NOT NULL,
	MetricTypeID	CHAR			NOT NULL,
	Title			VARCHAR  (100)	NOT NULL,
	Description		VARCHAR  (300)	NOT NULL,
	Track_Changed 	TIMESTAMP		NOT NULL ,
	Track_Userid	VARCHAR (50)	NOT NULL
) ;
CREATE TABLE objtrackerT_Measurement (
	ID			INT				NOT NULL,
	OrganizationID	INT			NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID)
		,PRIMARY KEY(OrganizationID,ID),
	ObjectiveID	INT				NOT NULL,
 	PeriodStarting	DATETIME			NOT NULL ,
	Measurement	VARCHAR	(64)	NULL ,
	Notes			VARCHAR  (2048)	NULL,
	Track_Changed TIMESTAMP			NOT NULL ,
	Track_Userid	VARCHAR (50)		NOT NULL
) ;
CREATE TABLE objtrackerT_Target ( 
	OrganizationID	INT			NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID),
	ID				INT			NOT NULL,
	FiscalYear		INT			NOT NULL ,
	Target			VARCHAR(16)	NOT NULL,
	Target1			VARCHAR(16)	NOT NULL,
	Target2			VARCHAR(16) 	NOT NULL,
	Track_Changed		TIMESTAMP		NOT NULL DEFAULT now(),
	Track_Userid		VARCHAR(50)	NOT NULL DEFAULT 'Init'
,PRIMARY KEY (OrganizationID, ID , FiscalYear) 
) ;
CREATE TABLE objtrackerT_Documentation (
	OrganizationID	INT			NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID),
	ID			INT				NOT NULL
		,PRIMARY KEY(OrganizationID,ID),
	ObjectiveID	INT				NOT NULL,
	PeriodStarting DATETIME 		NOT NULL ,
	Active		BIT				NOT NULL DEFAULT 1,
	Description	VARCHAR  (64)		NOT NULL,
	FileName		VARCHAR  (64)		NOT NULL,
	MimeType		VARCHAR  (64)		NOT NULL,
	Track_Added	TIMESTAMP			NOT NULL ,
	Track_Userid	VARCHAR (50)		NOT NULL
) ;
CREATE TABLE objtrackerT_FyCalendar ( 
	OrganizationID	INT			NOT NULL
		, FOREIGN KEY (OrganizationID) REFERENCES objtrackerT_Organization(ID)
	,PeriodStarting	DATETIME		NOT NULL
	,FrequencyID		CHAR			NOT NULL
		,PRIMARY KEY(OrganizationID,PeriodStarting,FrequencyID)
	,FiscalYear		INT			NOT NULL
) ;
CREATE TABLE objtrackerT_AuditTable (
	ID 				INT NOT NULL , PRIMARY KEY(ID)
	,Name 			VARCHAR (32)  NOT NULL
	,Description 	VARCHAR (32)  NULL 
	,Documentation 	VARCHAR (1024) NOT NULL 
) ;
CREATE TABLE objtrackerT_AuditColumn (
	ID 				INT NOT NULL , PRIMARY KEY(ID)
	,TableID		INT
	,Type 			char   NULL
	,DataType 		VARCHAR (32)  NOT NULL
	,Name 			VARCHAR (32)  NOT NULL
	,Description	VARCHAR (32)  NULL 
	,Documentation 	VARCHAR (1024) NOT NULL 
) ;
CREATE TABLE objtrackerT_AuditIndex (
	Track_CallerOrg    INT,
	Track_Guid 		VARCHAR(64)   ,
	Track_Date 		DateTime NOT NULL ,
	Track_Userid 	VARCHAR (50)  NOT NULL  ,
	Track_TableID 	INT NOT NULL 		,
	Track_ID 		INT NOT NULL,
	Track_PID 		INT NOT NULL,
	Track_Action 	VARCHAR (1) NOT NULL ,
	Track_Name 		VARCHAR (64) NOT NULL  ,
PRIMARY KEY (Track_CallerOrg,Track_Guid,Track_Date, Track_Userid,Track_TableID,Track_ID) 
) ; 
CREATE  INDEX IobjtrackerX_AuditIndex_Time
    ON objtrackerT_AuditIndex ( Track_Date );
CREATE TABLE objtrackerT_Audit (
	Track_CallerOrg    INT,
	Track_Guid 		VARCHAR(64)    ,
	Track_Date 		DateTime NOT NULL ,
	Track_Userid 	VARCHAR (50)  NOT NULL  ,
	Track_TableID 	INT NOT NULL 		,
	Track_ID 		INT NOT NULL ,
	Track_ColumnID 	INT NOT NULL 		,
	Track_Action 	VARCHAR (1) NOT NULL ,
	Track_SubAction VARCHAR (1) NOT NULL ,
	Track_Before	VARCHAR (1024)  NOT NULL ,
	Track_After 	VARCHAR (1024)  NOT NULL ,
PRIMARY KEY (Track_CallerOrg,Track_Guid,Track_Date, Track_Userid,Track_TableID,Track_ID,Track_ColumnID) 
) ;
CREATE INDEX IobjtrackerX_Audit_Time ON objtrackerT_Audit ( Track_Date );
