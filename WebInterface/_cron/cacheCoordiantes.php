<?php

// kill after 5 minutes
set_time_limit(300);

// change the current working directory to this file's.
// (so we can use relative references)
chdir(dirname(__FILE__));
require('../db_connect.php');

$SQL = "REPLACE DELAYED INTO `tweet_coordinates_by_school` (`lat`,`lng`,`school_id`,`weight`)

SELECT ROUND(latitude,5) AS lat, ROUND(longitude,5) AS lng, school_id, COUNT(*) AS `weight`
FROM tweets
GROUP BY lat, lng;";

$conn = connect_to_db();

echo "Starting coordinate caching...";
mysqli_query($conn, $SQL) or die(mysqli_error($con));
echo "Coordinate caching finished!";
?>

