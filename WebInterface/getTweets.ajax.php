<?php
require('db_connect.php');
$con = connect_to_db();
$school_id = intval($_GET["school_id"]);

$limit = is_numeric($_GET["limit"]) ? intval($_GET["limit"]) : 10;
$limit = 50;

$sw_lat = floatval($_GET["sw"]["lat"]);
$sw_lng = floatval($_GET["sw"]["lng"]);
$ne_lat = floatval($_GET["ne"]["lat"]);
$ne_lng = floatval($_GET["ne"]["lng"]);

$coords = " AND `t`.`latitude` BETWEEN $sw_lat AND $ne_lat
AND `t`.`longitude` BETWEEN $sw_lng AND $ne_lng ";

$contains = "";
if(isset($_GET["word"])){
    $contains = " AND lower(`t`.`text`) LIKE '%".strtolower($_GET["word"])."%' ";
}

$user_clause = "";
if($_GET["user_only"]){
    $user_id = intval($_GET["twitter_user_id"]);
    $user_clause = " AND `t`.`twitter_user_id` = '$user_id' ";
}

$hashtag = "";
if($_GET["hashtag_only"] == "true"){
    $coords = "";
    $contains = "";
    $user_clause = "";
    $str = $_GET["hashtag"];
    $hashtag = " AND t.tweet_id IN (SELECT tweet_id FROM hashtags WHERE hashtag='$str') ";
}

$table = "tweets";

$SQL = "SELECT `t`.`text`,`t`.`tweet_id`, `u`.`screen_name`, `u`.`name`, DATE_FORMAT(`t`.`datetime_entered`,'%M %D, %Y') AS `readable_date`, t.latitude, t.longitude 
FROM `$table` AS `t` JOIN `users` AS `u` 
ON `u`.`twitter_user_id` = `t`.`twitter_user_id` 
WHERE 
`t`.`school_id` = '$school_id' 
$hashtag
$coords
$contains 
$user_clause 
LIMIT $limit;";

//echo json_encode(array("test" => $SQL));
//die();

function makeClickableLinks($s) {
   return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
}

function makeMentionsLinks($s) {

  $patterns = array(
     '/@([A-Za-z0-9_]{1,18})/',
     '/#([A-Za-z0-9_]{1,18})/',
  );
  $replace = array(
    '<a href="https://twitter.com/$1" target="_blank">@$1</a>',
    '<a href="https://twitter.com/search?q=%23$1&amp;src=hash" target="_blank">#$1</a>',
  );
  return preg_replace($patterns, $replace, $s);
}


function format_tweets($tweet_results) {
        $tweets = array();
        while($result = mysqli_fetch_array($tweet_results)){
            $tweets[] = array(
                "tweet_id" => $result["tweet_id"],
                "text" => makeMentionsLinks(makeClickableLinks($result["text"])), 
                "screen_name" => $result["screen_name"],
                "name" => $result["name"],
                "date" => $result["readable_date"],
                "latitude" => $result["latitude"],
                "longitude" => $result["longitude"],
            ); 
        }
    return $tweets;
}

switch($_GET["request"]){
    case 'users_tweets':
        $user_id = intval($_GET["twitter_user_id"]);

        $SQL = "SELECT `t`.`text`,`t`.`tweet_id`, `u`.`screen_name`, `u`.`name`, DATE_FORMAT(`t`.`datetime_entered`,'%M %D, %Y') AS `readable_date`, t.latitude, t.longitude 
        FROM `tweets` AS `t` JOIN `users` AS `u` 
            ON `u`.`twitter_user_id` = `t`.`twitter_user_id` 
        WHERE 
            `t`.`school_id` = '$school_id' 
            AND t.twitter_user_id = '$user_id' 
        ORDER BY `t`.`datetime_entered` DESC
        LIMIT $limit;";
        $tweet_results = mysqli_query($con, $SQL) or die(mysqli_error($con));

        $tweets = format_tweets($tweet_results);
        break;

    case 'tweets_containing_hashtag':
        $str = $_GET["hashtag"];
        $SQL = "SELECT `t`.`text`,`t`.`tweet_id`, `u`.`screen_name`, `u`.`name`, DATE_FORMAT(`t`.`datetime_entered`,'%M %D, %Y') AS `readable_date`, t.latitude, t.longitude 
        FROM `tweets` AS `t` JOIN `users` AS `u` 
            ON `u`.`twitter_user_id` = `t`.`twitter_user_id` 
        WHERE 
            `t`.`school_id` = '$school_id' 
             AND t.tweet_id IN (SELECT tweet_id FROM hashtags WHERE hashtag='$str') 
        ORDER BY `t`.`datetime_entered` DESC
        LIMIT $limit;";
        $tweet_results = mysqli_query($con, $SQL) or die(mysqli_error($con));

        $tweets = format_tweets($tweet_results);
        break;

}

/*
$count_results = mysqli_query($con, $COUNT_SQL) or die(mysqli_error($con));
$top_users_results = mysqli_query($con, $TOP_USER_SQL) or die(mysqli_error($con));

$tweets = array();
while($result = mysqli_fetch_array($tweet_results)){
    $tweets[] = array(
        "tweet_id" => $result["tweet_id"],
        "text" => makeMentionsLinks(makeClickableLinks($result["text"])), 
        "screen_name" => $result["screen_name"],
        "name" => $result["name"],
        "date" => $result["readable_date"],
        "latitude" => $result["latitude"],
        "longitude" => $result["longitude"],
    ); 
}

$top_users = array();
while($user = mysqli_fetch_array($top_users_results)){
    $top_users[] = array(
        "num_tweets" => $user["total"],
        "screen_name" => $user["screen_name"],
        "name" => $user["name"],
        "twitter_user_id" => $user["twitter_user_id"],
    );
}
*/
$response = array("get" => $_GET, 
    "tweets"=>$tweets, 
);

echo json_encode($response);
?>
