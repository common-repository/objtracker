DROP FUNCTION IF EXISTS objtrackerF_StatusInteger;
DELIMITER $$
/*
	Return the image name representing the status of an integer measurement based on the target and milestones
 select 'g',objtrackerF_StatusInteger('201049','201099','201000','201025','201050')
 select 'y',objtrackerF_StatusInteger('201024','201099','201000','201025','201050')
 select objtrackerF_StatusInteger('201049','201099','201000','201025','201050')
*/
CREATE FUNCTION objtrackerF_StatusInteger(
	my_Measure	VARCHAR(32)
	,my_Target	VARCHAR(32)
	,my_Green		VARCHAR(32)
	,my_Yellow	VARCHAR(32)
)
RETURNS  VARCHAR(30) DETERMINISTIC
BEGIN
	DECLARE my_iMeasure, my_iTarget, my_iRed, my_iYellow, my_iGreen INT;

	SET my_iMeasure	:= CAST(REPLACE(my_Measure,',','') AS UNSIGNED INTEGER);
	SET my_iTarget	:= CAST(REPLACE(my_Target,',','') AS UNSIGNED INTEGER);
	SET my_iGreen	:= CAST(REPLACE(my_Green,',','') AS UNSIGNED INTEGER);
	SET my_iYellow	:= CAST(REPLACE(my_Yellow,',','') AS UNSIGNED INTEGER);

	return objtrackerF_StatusCompare(my_iMeasure,my_iTarget,my_iGreen,my_iYellow);
END
$$
