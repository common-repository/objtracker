DROP FUNCTION IF EXISTS objtrackerF_FiscalYearOfDate;
DELIMITER $$
/*
	Return the current fiscal year based on the input 1st month and the input date.
*/
CREATE FUNCTION objtrackerF_FiscalYearOfDate(
	my_FirstMonth	INT
	,my_DateTime	DateTime
) RETURNS  INT DETERMINISTIC
BEGIN
	DECLARE my_rv INT ;
	IF Month(my_DateTime) >= my_FirstMonth THEN
		SET my_rv := Year(my_DateTime) ;
	ELSE
		SET my_rv := Year(my_DateTime)-1 ;
	END IF;

	return my_rv ;
END
$$
