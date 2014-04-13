<?php

// kill after 5 minutes
set_time_limit(300);

// change the current working directory to this file's.
// (so we can use relative references)
chdir(dirname(__FILE__));
require('../db_connect.php');


$count_tweets_per_school = "
UPDATE schools , ( SELECT COUNT(*) AS total,school_id FROM tweets GROUP BY school_id ) c SET schools.num_tweets = c.total WHERE schools.school_id = c.school_id";

$count_users_per_school = "
UPDATE
    schools , ( SELECT COUNT(DISTINCT twitter_user_id) AS total, school_id FROM `tweets` GROUP BY school_id ) c
    SET
        schools.num_twitter_users = c.total  
        WHERE
            schools.school_id = c.school_id";

$calc_ave_per_school = "
UPDATE  `schools` SET  `ave_per_user` = ROUND(  `num_tweets` /  `num_twitter_users` , 3 ),  `ave_per_student` = ROUND(  `num_tweets` /  `students` , 3 )";

$conn = connect_to_db();


echo "Starting school count caching...";
mysqli_query($conn, $count_tweets_per_school) or die(mysqli_error($conn));
mysqli_query($conn, $count_tweets_per_school) or die(mysqli_error($conn));
mysqli_query($conn, $calc_ave_per_school) or die(mysqli_error($conn));
echo "caching finished!";
?>

