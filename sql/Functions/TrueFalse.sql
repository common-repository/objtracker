DROP FUNCTION IF EXISTS objtrackerF_TrueFalse;
DELIMITER $$
CREATE FUNCTION objtrackerF_TrueFalse(
	my_Bit	BIT
) RETURNS VARCHAR(8) DETERMINISTIC
BEGIN
  return (CASE WHEN my_Bit = True Then 'True' ELSE 'False' END) ;
END
$$
