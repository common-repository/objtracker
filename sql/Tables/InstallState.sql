CREATE TABLE objtrackerT_InstallState (
	ID				INT				NOT NULL ,PRIMARY KEY(ID),
	DB_Version		INT				NOT NULL,
	DB_State		VARCHAR (16)	NOT NULL,
	DB_Changed 		TIMESTAMP		NOT NULL DEFAULT now(),
	DB_Userid		VARCHAR (50) 	NOT NULL
) ;

INSERT INTO objtrackerT_InstallState  (
	ID,DB_Version,DB_State,DB_Changed,DB_Userid
) VALUES ( 
	1,4,'Init',now(),'Install'
);

