<?php
$school_id = intval($_GET["id"]);
error_reporting(E_ALL);
require('../db_connect.php');

$SCHOOL_SQL = "SELECT *, (ne_lat + sw_lat)/2 AS lat, (ne_lng + sw_lng)/2 AS lng FROM schools WHERE school_id='$school_id' LIMIT 1";

$VOLUME_SQL = "
SELECT COUNT( * ) AS total, DATE_FORMAT( datetime_entered,  '%Y-%m-%d' ) AS readable_date
FROM tweets
WHERE school_id =  '$school_id'
GROUP BY readable_date
ORDER BY readable_date ASC";

$HASHTAGS = "SELECT hashtag, `total` FROM `hashtags_by_school` WHERE school_id='$school_id' ORDER BY `total` DESC LIMIT 5";

$con = connect_to_db(); 
$school = mysqli_query($con, $SCHOOL_SQL) or die(mysqli_error($con));
$volumes = mysqli_query($con, $VOLUME_SQL) or die(mysqli_error($con));
$hashtags = mysqli_query($con, $HASHTAGS) or die(mysqli_error($con));

$school = mysqli_fetch_array($school);
$lat = $school["lat"];
$lng = $school["lng"];
$name = $school["name"];

$points = array(array("Date", "Volume"));
while($volume = mysqli_fetch_array($volumes)){
    $points[] = array($volume["readable_date"], intval($volume["total"]));
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Heatmaps</title>
    <style>
      html, body, #map-canvas {
        height: 100%;
        width:100%;
        min-height:800px;
        margin: 0px;
        padding: 0px
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
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
        google.load("visualization", "1", {packages:["corechart"]});
        google.setOnLoadCallback(drawVolumeChart);
        function drawVolumeChart() {
            var data = google.visualization.arrayToDataTable(<?php echo json_encode($points); ?>);
            var options = {
                title: 'Volume by Day'
            };
            var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
            chart.draw(data, options);
       }
</script>
  </head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
  </body>
</html>
