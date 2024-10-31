DROP FUNCTION IF EXISTS objtrackerF_FiscalYear;
DELIMITER $$
/*
	Return the current fiscal year based on the current date.
	Assumes: Organization = 1
 -- select objtrackerF_FiscalYear()
*/
CREATE FUNCTION objtrackerF_FiscalYear(
	C_SiteID	INT
)
RETURNS  INT DETERMINISTIC
BEGIN
	DECLARE myRv, myMonth INT;
	SELECT FirstMonth INTO myMonth FROM objtrackerT_Organization WHERE ID = C_SiteID;
	IF Month(Now()) >= myMonth THEN
		SET myRv := Year(Now()) ;
	ELSE
		SET myRv := Year(Now())-1 ;
  END IF;
  return myRv;
END
$$
