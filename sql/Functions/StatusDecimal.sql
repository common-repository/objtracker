DROP FUNCTION IF EXISTS objtrackerF_StatusDecimal;
DELIMITER $$
/*
	Return the image name representing the status of a decimal measurement based on the target and milestones
 select objtrackerF_StatusDecimal('8','10','6','2')
*/
CREATE FUNCTION objtrackerF_StatusDecimal(
	my_Measure	VARCHAR(32)
	,my_Target	VARCHAR(32)
	,my_Green		VARCHAR(32)
	,my_Yellow	VARCHAR(32)
)
RETURNS  VARCHAR(30) DETERMINISTIC
BEGIN
	DECLARE my_iMeasure , my_iTarget ,my_iRed ,my_iYellow ,my_iGreen FLOAT;
	
	SET my_iMeasure	:= CAST(REPLACE(my_Measure,',','') AS UNSIGNED INTEGER);
	SET my_iTarget	:= CAST(REPLACE(my_Target,',','') AS UNSIGNED INTEGER);
	SET my_iGreen	  := CAST(REPLACE(my_Green,',','') AS UNSIGNED INTEGER);
	SET my_iYellow	:= CAST(REPLACE(my_Yellow,',','') AS UNSIGNED INTEGER);

	return objtrackerF_StatusCompare(my_iMeasure,my_iTarget,my_iGreen,my_iYellow);
END
$$
