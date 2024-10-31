DROP FUNCTION IF EXISTS objtrackerF_StatusRatio;
DELIMITER $$
/*
Return the image name representing the status of a ratio measurement based on the target and milestones
 select objtrackerF_StatusRatio('4:1','4:1','4:1','4:1','4:1')
 select objtrackerF_StatusRatio('4:1 ','4:1 ','4:1 ','4:1 ','4:1 ')
*/
CREATE FUNCTION objtrackerF_StatusRatio(
	my_Measure	VARCHAR(32)
	,my_Target	VARCHAR(32)
	,my_Green	VARCHAR(32)
	,my_Yellow	VARCHAR(32)
)
RETURNS  VARCHAR(30) DETERMINISTIC
BEGIN
	DECLARE my_iMeasure1, my_iTarget1, my_iYellow1, my_iGreen1 INT;
	DECLARE my_iMeasure2, my_iTarget2, my_iYellow2, my_iGreen2 INT;
	DECLARE my_CharIndex INT;

	SET my_CharIndex := INSTR(my_Measure,':');
	SET my_iMeasure1 := CAST(SUBSTR(my_Measure,1,my_CharIndex-1) AS UNSIGNED INTEGER);
	SET my_iMeasure2 := CAST(SUBSTR(my_Measure,my_CharIndex+1,LENGTH(my_Measure)-my_CharIndex) AS UNSIGNED INTEGER);
	SET my_CharIndex := INSTR(my_Target,':');
	SET my_iTarget1	:= CAST(SUBSTR(my_Target,1,my_CharIndex-1) AS UNSIGNED INTEGER);
	SET my_iTarget2	:= CAST(SUBSTR(my_Target,my_CharIndex+1,LENGTH(my_Measure)-my_CharIndex) AS UNSIGNED INTEGER);
	SET my_CharIndex:= INSTR(my_Green,':');
	SET my_iGreen1	:= CAST(SUBSTR(my_Green,1,my_CharIndex-1) AS UNSIGNED INTEGER);
	SET my_iGreen2	:= CAST(SUBSTR(my_Green,my_CharIndex+1,LENGTH(my_Measure)-my_CharIndex) AS UNSIGNED INTEGER);
	SET my_CharIndex:= INSTR(my_Yellow,':');
	SET my_iYellow1	:= CAST(SUBSTR(my_Yellow,1,my_CharIndex-1) AS UNSIGNED INTEGER);
	SET my_iYellow2	:= CAST(SUBSTR(my_Yellow,my_CharIndex+1,LENGTH(my_Measure)-my_CharIndex) AS UNSIGNED INTEGER);

	return objtrackerF_StatusCompare(
		my_iMeasure1/my_iMeasure2
		,my_iTarget1/my_iTarget2
		,my_iGreen1/my_iGreen2
		,my_iYellow1/my_iYellow2
	);
END
$$
