DROP FUNCTION IF EXISTS objtrackerF_StatusDate;
DELIMITER $$
/*
	Return the image name representing the status of a date measurement based on the target and milestones
							  Measure	  Target	  Green	  Yellow
 select 'g',objtrackerF_StatusDate('2012-10-10','2012-10-01','2012-10-17','2012-10-27')
 select 'y',objtrackerF_StatusDate('10/19/2012','10/05/2012','10/20/2012','10/18/2012')
 select 'r', objtrackerF_StatusDate('10/30/2012','10/05/2012','10/20/2012','10/18/2012')
 select 'c', objtrackerF_StatusDate('10/01/2012','10/05/2012','10/20/2012','10/18/2012')
*/
CREATE FUNCTION objtrackerF_StatusDate(
	my_Measure	VARCHAR(32)
	,my_Target	VARCHAR(32)
	,my_Green	VARCHAR(32)
	,my_Yellow	VARCHAR(32)
)
RETURNS  VARCHAR(30) DETERMINISTIC
BEGIN
	return objtrackerF_StatusCompare(
		DATEDIFF(my_Target,my_Measure)
		,0
		,DATEDIFF(my_Target,my_Green)
		,DATEDIFF(my_Target,my_Yellow)
	);
END
$$
