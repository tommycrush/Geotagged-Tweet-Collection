<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Heatmaps</title>
    <style>
      html, body, #map-canvas {
        height: 100%;
        width:100%;
        margin: 0px;
        padding: 0px
      }
      div.tab-pane {
        width:90%;
      }
      ul.nav-tabs {
        padding-top:15px;

      }
    </style>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css">

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=visualization,drawing"></script>
    <style>
    thead > tr, tbody{
            display:block;}
            </style>
<?php
$school_id = intval($_GET["id"]);
require('db_connect.php');

# Quick, hacky way. More efficient: run a multi_query operation.
$SCHOOL_SQL = "SELECT *, (ne_lat + sw_lat)/2 AS lat, (ne_lng + sw_lng)/2 AS lng FROM schools WHERE school_id='$school_id' LIMIT 1";
$SQL = "SELECT lat, lng, weight FROM tweet_coordinates_by_school WHERE school_id='$school_id' ORDER BY weight DESC LIMIT 21000";
$COUNT = "SELECT COUNT(*) AS total FROM tweets WHERE school_id='$school_id'";
$USERS = "SELECT * FROM top_users_per_school WHERE school_id='$school_id' ORDER BY total DESC LIMIT 25";
$HASHTAGS = "SELECT hashtag, `total` FROM `hashtags_by_school` WHERE school_id='$school_id' ORDER BY `total` DESC LIMIT 50";

$con = connect_to_db(); 
$school = mysqli_query($con, $SCHOOL_SQL) or die(mysqli_error($con));
$results = mysqli_query($con, $SQL) or die(mysqli_error($con));
$count = mysqli_query($con, $COUNT) or die(mysqli_error($con));
$top_users = mysqli_query($con, $USERS) or die(mysqli_error($con));
$hashtags = mysqli_query($con, $HASHTAGS) or die(mysqli_error($con));

$school = mysqli_fetch_array($school);
$lat = $school["lat"];
$lng = $school["lng"];
$name = $school["name"];

$points = array();

// compose the javascript objects to pass to the heatmap
$count = mysqli_fetch_array($count);
$num_tweets = intval($count["total"]);
while($result = mysqli_fetch_array($results)){
    $points[] = "{location: new google.maps.LatLng(".$result["lat"].", ".$result["lng"]."), weight: ".$result["weight"]."}";
}
?>

<script>
echo 'var school_id = '.$school_id.';';
// tabs javascript
$(document).ready( function () {
    $('a[data-toggle="tab"]').click(function (e) {
          e.preventDefault();
            $(this).tab('show');
    });
});

// Adding 500 Data Points
var map, pointarray, heatmap, rectangle, progress_function;
var markers = [];

function initialize() {
  var mapOptions = {
    zoom: 15,
    center: new google.maps.LatLng(<?php echo $lat.",".$lng; ?>),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

  map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);

  var tweetsWithWeights = [<?php echo implode(",",$points); ?>];
  var pointArray = new google.maps.MVCArray(tweetsWithWeights);

  heatmap = new google.maps.visualization.HeatmapLayer({
    data: pointArray
  });

  heatmap.setMap(map);
 
  rectangle = null;
}

function addRectToMap(){
    if(rectangle){
        // clear existing
        rectangle.setMap(null);
        rectangle = null;
    }
    
    var c = map.getCenter();
    var bounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(c.lat(), c.lng()),
        new google.maps.LatLng(c.lat()+0.003, c.lng()+0.003)
    );

    rectangle = new google.maps.Rectangle({
        bounds: bounds,
        editable: true,
        draggable: true
    });
 
    rectangle.setMap(map); 
}

function getTweetsInRect(){
    var ne = rectangle.getBounds().getNorthEast();
    var sw = rectangle.getBounds().getSouthWest();

    var contentString = '<b>Rectangle moved.</b><br>' +
    'New north-east corner: ' + ne.lat() + ', ' + ne.lng() + '<br>' +
    'New south-west corner: ' + sw.lat() + ', ' + sw.lng();
 
    var request_data = {
        'ne' : {
            'lat' : ne.lat(),
            'lng' : ne.lng()
        },
        'sw' : {
            'lat' : sw.lat(),
            'lng' : sw.lng()
        },
        'limit': 5,
        'school_id' : school_id
    };

    if($("#word").val()){
        request_data["word"] = $("#word").val();
    }

    $.ajax('getTweets.ajax.php',{
       dataType: 'json',
       success: function(data){
         console.log(data);
         stopProgress(); 
         fillTweetsBox(data.tweets);
         fillTopUsersTable(data.top_users);
       },
       error: function(jqXHR, text){
            stopProgress(); 
            console.log(jqXHR);
            console.log(text);
       },
       data: request_data
    });

    startProgress();
}

function stopProgress(){
    $("#tweets_progress").html('');
    clearInterval(progress_function);
}

function startProgress(){
    var progress_bar = '<h3>Loading Tweets...</h3><br/><div style="width:300px;"><div class="progress progress-striped active"><div class="progress-bar"  role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"><span class="sr-only">0% Complete</span></div></div></div>';
    $("#tweets_progress").html(progress_bar);
    
    progress_function = setInterval(function() {
        console.log("called");
        var $bar = $('.progress-bar');

        if ($bar.width()>=300) {
            clearInterval(progress_function);
            $('.progress').removeClass('active');
        } else {
            $bar.width($bar.width()+12);
        }
        //$bar.text($bar.width()/4 + "%");
    }, 500);
}

function fillTopUsersTable(top_users){
    console.log(top_users);
    var html = "<thead><tr><td>Screen Name</td><td>Name</td><td># Tweets</td></tr></thead><tbody>";
    for(var i = 0; i < top_users.length; i = i + 1){
        user = top_users[i];
        html += "<tr><td><a href='#' onclick='loadTweetsOfUserID(" + user.twitter_user_id+ ", true); return false;'>@" + user.screen_name + "</a></td><td>" + user.name + "</td><td>" + user.num_tweets + "</td></tr>"; 
    }
    html += "</tbody>";
    $("#top_users").html(html);
}

function fillTweetsBox(tweets){
         var html = "";
         for(var i = 0; i < tweets.length; i = i+ 1){
            var tweet = tweets[i];
            html += createDivFromTweet(tweet);
         }
         $("#tweets").html(html);
}

function createDivFromTweet(tweet){
  var div = '<blockquote class="twitter-tweet" lang="en"><p>' + tweet.text + '</p>&mdash;' 
  div += tweet.name + ' (@' + tweet.screen_name + ') <a href="http://googe.com/">' + tweet.date + '</a>';
  div += '</blockquote>';
  return div;
}

function loadTweetsOfUserID(user_id, in_rectangle){
    startProgress();
 
    var request_data = {
        'limit': 50,
        'school_id': school_id,
        'request': 'users_tweets',
        'twitter_user_id' : user_id
    };

    if($("#word").val()){
        request_data["word"] = $("#word").val();
    }

    $.ajax('getTweets.ajax.php',{
       dataType: 'json',
       success: function(data){
         stopProgress();
         fillTweetsBox(data.tweets);
         $('#tweets_link').click();
         addMarkersToMap(data.tweets);
       },
       error: function(jqXHR, text){
            stopProgress();
            console.log(jqXHR);
            console.log(text);
       },
       data: request_data
    });
}

function loadTweetsOfHashtag(hashtag){
    var request_data = {
        'limit': 50,
        'school_id': school_id,
        'request' : 'tweets_containing_hashtag',
        'hashtag' : hashtag
    };

    $.ajax('getTweets.ajax.php',{
       dataType: 'json',
       success: function(data){
         stopProgress();
         fillTweetsBox(data.tweets);
         $('#tweets_link').click();
         addMarkersToMap(data.tweets);
       },
       error: function(jqXHR, text){
           stopProgress();
       },
       data: request_data
    });

    startProgress();
}


function clearMarkersFromMap(){
    for(var i = 0; i < markers.length; i = i + 1){
        markers[i].setMap(null);
    }
}

function addMarkersToMap(tweets){
   clearMarkersFromMap();
   markers = [];
   for(var i = 0; i < tweets.length; i = i + 1){
    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(tweets[i]["latitude"],tweets[i]["longitude"]),
        map: map,
        title: tweets[i]["text"]
    });
    markers.push(marker);
   }
}

google.maps.event.addDomListener(window, 'load', initialize);
</script>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
  </head>

  <body>
    <div class="row" style="height:100%;padding:0px;">
      <div class="col-md-8" style="height:100%;padding:0px;">
        <div id="map-canvas"></div>
      </div>
      <div class="col-md-4">
        <div class="row">
            <h3 class="text-muted"><?php echo $name; ?></h3>
        </div>
    
        <div class="row">
            <div id="tweets_progress"></div>
        </div>

        <div style="width:90%;">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tweets" data-toggle="tab" id="tweets_link">Tweets</a></li>
                <li><a href="#users" data-toggle="tab">Users</a></li>
                <li><a href="#hashtags" data-toggle="tab">Hashtags</a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="tweets" style="overflow:auto; height:500px;">
                    
                </div>
                
                <div class="tab-pane " id="users">
                    <table id="top_users" class="table table-striped">
                        <thead><tr><td>Screen Name</td><td>Name</td><td># Tweets</td></tr></thead>
                        <tbody>
                           <?php
                           while($row = mysqli_fetch_array($top_users)){
                               echo "<tr><td><a href='#' onclick='loadTweetsOfUserID(".$row['twitter_user_id'].", true); return false;'>@".$row['screen_name']."</a></td><td>".$row['name']."</td><td>".$row['total']."</td></tr>"; 
                            }
                            ?>
                         </tbody>
                    </table>
                </div>

                <div class="tab-pane " id="hashtags">
                    <div id="hashtag_stats" class="row" style="">
                        <table id="top_hashtags" style="width:100%;" class="table table-striped"> 
                            <thead><tr><td>Hashtag</td><td># Tweets</td></tr></thead>
                            <tbody style="height:500px; overflow:auto; ">
                            <?php
                            while($hashtag = mysqli_fetch_array($hashtags)){
                                echo '<tr><td><a href="#" onClick="loadTweetsOfHashtag(\''.$hashtag["hashtag"].'\'); return false;">';
                                echo '#'.$hashtag["hashtag"].'</a></td><td>'.$hashtag["total"].'</td></tr>';            
                            }
                            ?>
                            </tbody>
                        </table>
                    </div><!-- hashtag stats -->
                </div><!-- tab-pane-->
            </div>
        </div><!-- 90% width -->
        </div><!-- col-md-4 -->
     </div><!-- page row --> 
  </body>
</html>
