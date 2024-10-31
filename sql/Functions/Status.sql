DROP FUNCTION IF EXISTS objtrackerF_Status;
DELIMITER $$
/*
	Return the image name for the status of the measurement based on its target and milestones
 select objtrackerF_Status('I','10000','20100','20000','19,000')
 select objtrackerF_Status('I','10000','2010','20000','19,000')
 select objtrackerF_Status('P','100%','3%','$3','','ten')
 select objtrackerF_Status('P','100%','','$3','','ten')
 select objtrackerF_Status('P','100%','3%','$3','','')
 select objtrackerF_Status('k','100%','3%','$3','','ten')
 select objtrackerF_Status('P','100%','100%','100%','100%','100%')
 select objtrackerF_Status('1','8','8','8','8','8')
 select objtrackerF_Status('$','$43,320','$600,000 ','$500,000','$400,000')
*/
CREATE FUNCTION objtrackerF_Status(
	my_Type		CHAR		-- NumericTypeID
	,my_Measure	VARCHAR(32)	-- Measurment
	,my_Target	VARCHAR(32)	-- Target value
	,my_Green	VARCHAR(32)	-- Green milestone
	,my_Yellow	VARCHAR(32)	-- Yellow milestone
)
RETURNS  VARCHAR(128) DETERMINISTIC
BEGIN
	DECLARE my_rv VARCHAR(128);
	SET my_RV := '';
	IF my_Measure IS NULL THEN
		return 'LATE';
	END IF;
	IF my_Measure = '' THEN
		return 'LATE';
	END IF;
	IF my_Measure = 'Missing' THEN
		return 'Admin';
	END IF;
	IF my_Type = 'P' THEN       -- Percent
		return objtrackerF_StatusPercent(my_Measure,my_Target,my_Green,my_Yellow);
	ELSEIF my_Type = 'I' THEN   -- Integer
		return objtrackerF_StatusInteger(my_Measure,my_Target,my_Green,my_Yellow);
	ELSEIF my_Type = '$'  THEN  -- Dollar
		return objtrackerF_StatusDollar(my_Measure,my_Target,my_Green,my_Yellow);
	ELSEIF my_Type = 'D' THEN   -- Date
		return objtrackerF_StatusDate( -- 12/25/2012
		CONCAT(SUBSTR(my_Measure,7,4),'-',SUBSTR(my_Measure,1,2),'-',SUBSTR(my_Measure,4,2))
		,CONCAT(SUBSTR(my_Target,7,4),'-',SUBSTR(my_Target,1,2),'-',SUBSTR(my_Target,4,2))
		,CONCAT(SUBSTR(my_Green,7,4),'-',SUBSTR(my_Green,1,2),'-',SUBSTR(my_Green,4,2))
		,CONCAT(SUBSTR(my_Yellow,7,4),'-',SUBSTR(my_Yellow,1,2),'-',SUBSTR(my_Yellow,4,2))
		);
	ELSEIF my_Type = 'R' THEN   -- Ratio
		return objtrackerF_StatusRatio(my_Measure,my_Target,my_Green,my_Yellow);
	END IF;
	return 'DVLP';
END
$$
