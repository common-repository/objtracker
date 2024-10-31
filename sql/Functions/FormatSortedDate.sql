DROP FUNCTION IF EXISTS objtrackerF_FormatSortedDate;
/*
	Return the input date in YYYY-MM-DD format so that it sorts well.
*/
DELIMITER $$
/*
	Return input date in MM/DD/YYYY format.
*/
CREATE FUNCTION objtrackerF_FormatSortedDate (
	myDate	DateTime
) RETURNS  VARCHAR(10)
 DETERMINISTIC
BEGIN
	return Date_Format(myDate, '%Y/%m/%d');
END
$$
