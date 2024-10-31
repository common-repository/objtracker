DROP FUNCTION IF EXISTS objtrackerF_StatusCompare;
DELIMITER $$
/*
 select objtrackerF_StatusCompare(101,100,90,80)
 select objtrackerF_StatusCompare(90,100,90,80)
 select objtrackerF_StatusCompare(80,100,90,80)
 select objtrackerF_StatusCompare(70,100,90,80)

 select objtrackerF_StatusCompare(1,10,20,30)
 select objtrackerF_StatusCompare(20,10,20,30)
 select objtrackerF_StatusCompare(30,10,20,30)
 select objtrackerF_StatusCompare(40,10,20,30)
*/
CREATE FUNCTION objtrackerF_StatusCompare(
	my_Measure	FLOAT
	,my_Target	FLOAT
	,my_Green	FLOAT
	,my_Yellow	FLOAT
)
RETURNS  VARCHAR(30) DETERMINISTIC
BEGIN
	IF my_Target < my_Green AND my_Green < my_Yellow THEN
		IF my_Measure <= my_Target THEN
			return 'COMPLETE';
		END IF;
		IF my_Measure <= my_Green THEN
			return 'GREEN';
		END IF;
		IF my_Measure <=  my_Yellow THEN
			return 'YELLOW';
		ELSE
			return 'RED';
		END IF;
	ELSEIF my_Target > my_Green AND my_Green > my_Yellow THEN
		IF my_Measure >= my_Target THEN
			return 'COMPLETE';
		END IF;
		IF my_Measure >= my_Green THEN
			return 'GREEN';
		END IF;
  		IF my_Measure >= my_Yellow THEN
			return 'YELLOW';
		ELSE
			return 'RED';
		END IF;
	END IF;
	return 'ADMIN2';
END
$$
