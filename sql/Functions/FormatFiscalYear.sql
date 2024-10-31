/*
	ID of [objtrackerT_FiscalYear] and [objtrackerT_FyCalendar] represents the ID of the first year of the fiscal year.
*/
DROP FUNCTION IF EXISTS objtrackerF_FormatFiscalYear;
DELIMITER $$
CREATE FUNCTION objtrackerF_FormatFiscalYear(
	C_YearID INT
	,C_Month INT
) RETURNS  VARCHAR(16)
 DETERMINISTIC
BEGIN
 	SET @FakeDate := CONCAT( '2000-', CAST( C_Month AS CHAR(2)), '-01' );
	SET @MonthName := SUBSTR(MONTHNAME(STR_TO_DATE(@FakeDate, '%Y-%m-%d')),1,3);
	IF C_Month = 1 THEN
		return CONCAT( @MonthName, ' ', CAST( C_YearID AS CHAR(4) ) );
	ELSE
		return CONCAT( 
			@MonthName,' '
			,CAST(C_YearID AS CHAR(4)),' - '
			,CAST(C_YearID+1 AS CHAR(4)) );
	END IF;
END
$$
