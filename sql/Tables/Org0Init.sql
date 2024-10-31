	-- Organization
	INSERT INTO objtrackerT_Organization (
		ID, Title, ShortTitle, FirstMonth, UploadFsPath,Trailer,DefaultPassword, Track_Userid
	) VALUES (
		0,'Model Foundation','Model', 1, 'C:/Dan/Downloads','Trailer text','DefaultPassword','0Init'
	) ;

	-- Objective Type
	INSERT INTO objtrackerT_ObjectiveType  (
		OrganizationID,ID,Active,Title,Title2,Description,Track_Userid
	) VALUES (
		0,'C',1,'Customer','Customer','Measures that answer the question ''How do customers see us?''','0Init'
	) ;
	INSERT INTO objtrackerT_ObjectiveType  (
		OrganizationID,ID,Active,Title,Title2,Description,Track_Userid
	) VALUES (
		0,'E',1,'Employee Development','Staff','Measures that answer the question ''How can we continue to improve and create value?''','0Init'
	) ;
	INSERT INTO objtrackerT_ObjectiveType  (
		OrganizationID,ID,Active,Title,Title2,Description,Track_Userid
	) VALUES (
		0,'F',1,'Financials','Account','High-level financial measures that answer the question ''How do we look to shareholders?''','0Init'
	) ;
	INSERT INTO objtrackerT_ObjectiveType  (
		OrganizationID,ID,Active,Title,Title2,Description,Track_Userid
	) VALUES (
		0,'I',1,'Internal Business Process','Process','Measures that answer the question ''What must we excel at?''','0Init'
	) ;

	-- Frequency
	INSERT INTO objtrackerT_Frequency (
		OrganizationID,ID, Count_Months, WeeksToAlert, Title, Description
	) VALUES (
		0, 'A', 12, 51, 'Annual','Once per year'
	) ;
	INSERT INTO objtrackerT_Frequency (
		OrganizationID,ID, Count_Months, WeeksToAlert, Title, Description
	) VALUES (
		0,'B', 6, 25, 'Bi-Annual','Twice per year')
	;
	INSERT INTO objtrackerT_Frequency (
		OrganizationID,ID, Count_Months, WeeksToAlert, Title, Description
	) VALUES (
		0,'Q', 3, 12,'Quarterly','Four times per year')
	;
	INSERT INTO objtrackerT_Frequency (
		OrganizationID,ID, Count_Months, WeeksToAlert, Title, Description
	) VALUES (
		0, 'M', 1, 3, 'Monthly','12 times per year'
	);

	-- Metric Type
	INSERT INTO objtrackerT_MetricType (
		OrganizationID,ID,Title,Description,Track_Userid
	) VALUES (
		0,'$','Dollar','$nn,nnn,nnn','0Init'
	) ;
	INSERT INTO objtrackerT_MetricType (
		OrganizationID,ID,Title,Description,Track_Userid
	) VALUES (
		0,'D','Completion Date','mm/dd/yyyy','0Init'
	) ;
	INSERT INTO objtrackerT_MetricType (
		OrganizationID,ID,Title,Description,Track_Userid
	) VALUES (
		0,'I','Integer','nn,nnn,nnn','0Init'
	) ;
	INSERT INTO objtrackerT_MetricType (
		OrganizationID,ID,Title,Description,Track_Userid
	) VALUES (
		0,'P','Percentage','nnn%','0Init'
	) ;
	INSERT INTO objtrackerT_MetricType (
		OrganizationID,ID,Title,Description,Track_Userid
	) VALUES (
		0,'R','Ratio','nn:nn','0Init'
	) ;

