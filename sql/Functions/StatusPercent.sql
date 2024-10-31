DROP FUNCTION IF EXISTS objtrackerF_StatusPercent;
DELIMITER $$
/*
 Return the image name representing the status of a percent measurement based on the target and milestones
 select objtrackerF_StatusPercent('15.6%','100%','90%','75%','50%')
 select objtrackerF_StatusPercent('80%','80%','80%','80%','80%')
 select objtrackerF_StatusPercent('100% ','25%','25%','25%','25%')
 select objtrackerF_StatusPercent('100%','100%','100%','100%','100%')
*/
CREATE FUNCTION objtrackerF_StatusPercent(
	my_Measure	VARCHAR(32)
	,my_Target	VARCHAR(32)
	,my_Green	VARCHAR(32)
	,my_Yellow	VARCHAR(32)
)
RETURNS  VARCHAR(30) DETERMINISTIC
BEGIN
	DECLARE my_iMeasure, my_iTarget, my_iYellow, my_iGreen DECIMAL(9);
	
	SET my_Measure	:= REPLACE(SUBSTR(my_Measure,1,LENGTH(my_Measure)-1),',','') ;
	SET my_Target	:= REPLACE(SUBSTR(my_Target,1,LENGTH(my_Target)-1),',','') ;
	SET my_Green	:= REPLACE(SUBSTR(my_Green,1,LENGTH(my_Green)-1),',','') ;
	SET my_Yellow	:= REPLACE(SUBSTR(my_Yellow,1,LENGTH(my_Yellow)-1),',','') ;
	SET my_iMeasure	:= CAST(my_Measure AS DECIMAL(9));
	SET my_iTarget	:= CAST(my_Target AS DECIMAL(9));
	SET my_iGreen	:= CAST(my_Green AS DECIMAL(9));
	SET my_iYellow	:= CAST(my_Yellow AS DECIMAL(9));

	return objtrackerF_StatusCompare(my_iMeasure,my_iTarget,my_iGreen,my_iYellow);
END
$$
