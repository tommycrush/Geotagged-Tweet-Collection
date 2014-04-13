-- NOTE:
-- We used these procedures to seperate the hashtags
-- comma delimited field in our original table to
-- a table that handles hashtag<->tweet_id associations

-- We first ran "CALL dumpHashtags();" to do all the hastags up till now
-- Then we ran "CALL storeHashtagsOfLastThreeDays();" every other
-- day to run the same process on newly created tweets.

-- Server version: 5.5.34
-- PHP Version: 5.3.10-1ubuntu3.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `twitter`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `dumpHashtags`()
BEGIN
  DECLARE done BOOLEAN DEFAULT FALSE;
  DECLARE _hashtags VARCHAR(255);
  DECLARE _tweet_id BIGINT UNSIGNED;
  DECLARE cur CURSOR FOR SELECT tweet_id, hashtags FROM tweets;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done := TRUE;

  OPEN cur;

  testLoop: LOOP
    FETCH cur INTO _tweet_id, _hashtags;
    IF done THEN
      LEAVE testLoop;
    END IF;
    CALL dumpThisTweetsHashtags(_tweet_id, _hashtags);
  END LOOP testLoop;

  CLOSE cur;
 END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `dumpThisTweetsHashtags`(tweet_id BIGINT, hashtags VARCHAR(255))
BEGIN
      DECLARE a INT Default 0 ;
      DECLARE str VARCHAR(255);
      simple_loop: LOOP
         SET a=a+1;
         SET str=SPLIT_STR(hashtags,",",a);
         IF str='' THEN
            LEAVE simple_loop;
         END IF;
         #Do Inserts into temp table here with str going into the row
         insert ignore into hashtags (`hashtag`,`tweet_id`)values (str, tweet_id);
   END LOOP simple_loop;

 END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `storeHashtagsOfLastThreeDays`()
BEGIN
  DECLARE done BOOLEAN DEFAULT FALSE;
  DECLARE _hashtags VARCHAR(255);
  DECLARE _tweet_id BIGINT UNSIGNED;
  DECLARE cur CURSOR FOR SELECT tweet_id, hashtags FROM tweets WHERE datetime_entered >= DATE_ADD(CURDATE(), INTERVAL -3 DAY);
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done := TRUE;

  OPEN cur;

  testLoop: LOOP
    FETCH cur INTO _tweet_id, _hashtags;
    IF done THEN
      LEAVE testLoop;
    END IF;
    CALL dumpThisTweetsHashtags(_tweet_id, _hashtags);
  END LOOP testLoop;

  CLOSE cur;
 END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `DELETE_DOUBLE_SPACES`( title VARCHAR(250) ) RETURNS varchar(250) CHARSET latin1
    DETERMINISTIC
BEGIN
    DECLARE result VARCHAR(250);
    SET result = REPLACE( title, '  ', ' ' );
    WHILE (result <> title) DO 
        SET title = result;
        SET result = REPLACE( title, '  ', ' ' );
    END WHILE;
    RETURN result;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `LFILTER`( title VARCHAR(250) ) RETURNS varchar(250) CHARSET latin1
    DETERMINISTIC
BEGIN
    WHILE (1=1) DO
        IF( ASCII(title) BETWEEN ASCII('a') AND ASCII('z')
            OR ASCII(title) BETWEEN ASCII('A') AND ASCII('Z')
            OR ASCII(title) BETWEEN ASCII('0') AND ASCII('9')
        ) THEN
            SET title = LOWER( title );
            SET title = REPLACE(
                REPLACE(
                    REPLACE(
                        title,
                        CHAR(10), ' '
                    ),
                    CHAR(13), ' '
                ) ,
                CHAR(9), ' '
            );
            SET title = DELETE_DOUBLE_SPACES( title );
            RETURN title;
        ELSE
            SET title = SUBSTRING( title, 2 );          
        END IF;
    END WHILE;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `SPLIT_STR`(
  x VARCHAR(255),
  delim VARCHAR(12),
  pos INT
) RETURNS varchar(255) CHARSET latin1
RETURN REPLACE(SUBSTRING(SUBSTRING_INDEX(x, delim, pos),
       LENGTH(SUBSTRING_INDEX(x, delim, pos -1)) + 1),
       delim, '')$$

DELIMITER ;

-- --------------------------------------------------------