<?php

// kill after 5 minutes
set_time_limit(300);

// change the current working directory to this file's.
// (so we can use relative references)
chdir(dirname(__FILE__));
require('../db_connect.php');

$dump = "CALL storeHashtagsOfLastThreeDays();";

$count_hashtags_by_school = "
REPLACE INTO hashtags_by_school  (hashtag, school_id, `total`) 
SELECT h.`hashtag`, t.`school_id`, COUNT(*) AS `total` 
FROM  `hashtags` AS  `h` ,  `tweets` AS  `t` 
WHERE h.tweet_id = t.`tweet_id` 
GROUP BY  `h`.`hashtag` , t.school_id;
";

$conn = connect_to_db();


echo "Starting hashtag caching...";
mysqli_query($conn, $dump) or die(mysqli_error($conn));
mysqli_query($conn, $count_hashtags_by_school) or die(mysqli_error($conn));
echo " finished!";
?>

