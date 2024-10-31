/*
	ID of [objtrackerT_FiscalYear] and [objtrackerT_FyCalendar] represents the ID of the first year of the fiscal year.
*/
DROP PROCEDURE IF EXISTS objtrackerP_FiscalYearInsert;
DELIMITER $$
/*
	CALL objtrackerP_FiscalYearInsert (2000,'2000-Test','me');
  delete from objtrackerT_FiscalYear WHERE ID  > 1999;
  delete from objtrackerT_FyCalendar WHERE FiscalYear > 1999;
  select * from objtrackerT_FiscalYear;
  select * from objtrackerT_FyCalendar order by fiscalyear, frequencyid, periodstarting;
  SET SQL_SAFE_UPDATES=0;
*/
CREATE PROCEDURE objtrackerP_FiscalYearInsert(
	C_CallerOrg			INT
	,C_CallerUser		INT
	,C_ID				INT				-- Fiscal Year
)
BEGIN
	DECLARE C_FirstMonth INT;
	DECLARE C_StartString VARCHAR (32);
	DECLARE C_StartFY DateTime;
	SELECT FirstMonth INTO C_FirstMonth FROM objtrackerT_Organization WHERE ID = C_CallerOrg;

	IF (C_ID IS NULL OR C_ID = '') THEN
		SELECT 'FyInsertYear' AS C_ErrorID, 'Fiscal Year field is required' AS C_ErrorMessage;
	ELSEIF EXISTS (SELECT * FROM objtrackerT_FiscalYear WHERE OrganizationID = C_CallerOrg AND ID = C_ID) THEN
		SELECT 'FyInsertDup' AS C_ErrorID, 'Fiscal Year  already exists' AS C_ErrorMessage;
	ELSE
		SELECT UserName INTO @UserName FROM objtrackerT_Person WHERE OrganizationID = C_CallerOrg AND ID = C_CallerUser;
	
		IF C_FirstMonth = 1 THEN
			SET @Title := CAST( C_ID AS CHAR(4) );
		ELSE
			SET @Title := CONCAT(
							CAST( C_ID AS CHAR(4) ),
							'-',
							Substr( CAST( (C_ID+1) AS CHAR(4) ),3,2)
						);
		END IF;

		SET C_StartString :=  CONCAT(CAST(C_ID AS CHAR(4)), '-', CAST(C_FirstMonth AS CHAR(2)) , '-01 00:00:00');
		Set C_StartFY := CAST(C_StartString AS DateTime);
		-- Add to FiscalYear table
		INSERT INTO objtrackerT_FiscalYear (
			ID
			,OrganizationID
			,Active
			,Title
			,Track_Changed
			,Track_Userid
		) VALUES (
			C_ID
			,C_CallerOrg
			,1
			,@Title
			,now()
			,@UserName
		);
	
		-- Annual frequency added to the calendar
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,C_StartFY,'A',C_ID);
		-- Bi-annual frequency added to the calendar
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,C_StartFY,'B',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 6 MONTH),'B',C_ID);

  		-- Quarterly frequency added to the calendar
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
  			VALUES ( C_CallerOrg,C_StartFY,'Q',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
	  		VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 3 MONTH),'Q',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 6 MONTH),'Q',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 9 MONTH),'Q',C_ID);
		-- Monthly frequency added to the calendar
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,C_StartFY,'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 1 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 2 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 3 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 4 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 5 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 6 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 7 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 8 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 9 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 10 MONTH),'M',C_ID);
		INSERT INTO objtrackerT_FyCalendar (OrganizationID,PeriodStarting,FrequencyID,FiscalYear)
			VALUES ( C_CallerOrg,Date_Add(C_StartFY, INTERVAL 11 MONTH),'M',C_ID);

		SELECT '' AS C_ErrorID, '' AS C_ErrorMessage;
	END IF;
END
$$
