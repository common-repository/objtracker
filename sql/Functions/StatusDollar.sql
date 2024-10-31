DROP FUNCTION IF EXISTS objtrackerF_StatusDollar;
/*
*/
DELIMITER $$
CREATE FUNCTION objtrackerF_StatusDollar(
	my_Measure	VARCHAR(32)
	,my_Target	VARCHAR(32)
	,my_Green		VARCHAR(32)
	,my_Yellow	VARCHAR(32)
)
RETURNS  VARCHAR(30) DETERMINISTIC
BEGIN
	DECLARE my_iMeasure, my_iTarget, my_iRed, my_iYellow, my_iGreen INT;
	SET my_iMeasure	:= CAST(REPLACE(SUBSTR(my_Measure,2,LENGTH(my_Measure)-1),',','') AS UNSIGNED INTEGER);
	SET my_iTarget	:= CAST(REPLACE(SUBSTR(my_Target, 2,LENGTH(my_Target)-1), ',','') AS UNSIGNED INTEGER);
	SET my_iGreen	:= CAST(REPLACE(SUBSTR(my_Green,  2,LENGTH(my_Green)-1),  ',','') AS UNSIGNED INTEGER);
	SET my_iYellow	:= CAST(REPLACE(SUBSTR(my_Yellow, 2,LENGTH(my_Yellow)-1), ',','') AS UNSIGNED INTEGER);
	return objtrackerF_StatusCompare(my_iMeasure,my_iTarget,my_iGreen,my_iYellow);
END
$$
