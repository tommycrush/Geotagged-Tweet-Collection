<?php

// kill after 15 minutes
set_time_limit(900);

// change the current working directory to this file's.
// (so we can use relative references)
chdir(dirname(__FILE__));
require('../db_connect.php');

function sql_for_school($id) {
    return "SELECT COUNT( * ) AS total,  `u`.`screen_name` ,  `u`.`name` , t.twitter_user_id, t.school_id
    FROM  `tweets` AS  `t` 
    JOIN  `users` AS  `u` ON  `u`.`twitter_user_id` =  `t`.`twitter_user_id` 
    WHERE  `t`.`school_id` =  '".$id."'
    GROUP BY t.twitter_user_id
    ORDER BY total DESC 
    LIMIT 25";
}

$sqls = array();
for($school_id = 1; $school_id < 650; $school_id++){
    $sqls[] = "(".sql_for_school($school_id).")";
}

$final_sql = implode(" UNION ALL ", $sqls);

$SQL = "REPLACE INTO top_users_per_school (`total`,`screen_name`,`name`,`twitter_user_id`,`school_id`) ".$final_sql;

$conn = connect_to_db();

echo "Starting top user caching...";
mysqli_query($conn, $SQL) or die(mysqli_error($con));
echo "User caching finished!";
?>

