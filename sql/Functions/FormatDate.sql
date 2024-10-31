DROP FUNCTION IF EXISTS objtrackerF_FormatDate;
DELIMITER $$
/*
	Return input date in MM/DD/YYYY format.
*/
CREATE FUNCTION objtrackerF_FormatDate (
	myDate	DateTime
) RETURNS  VARCHAR(10)
 DETERMINISTIC
BEGIN
	return Date_Format(myDate, '%m/%d/%Y');
END
$$
