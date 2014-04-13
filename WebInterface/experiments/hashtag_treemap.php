<?php
$school_id = intval($_GET["id"]);
error_reporting(E_ALL);
require('../db_connect.php');

$HASHTAGS = "SELECT lower(hashtag) AS hashtag, SUM(`total`) AS sum_total FROM `hashtags_by_school` GROUP BY lower(hashtag) ORDER BY sum_total DESC LIMIT 100";  

$con = connect_to_db(); 
$hashtags = mysqli_query($con, $HASHTAGS) or die(mysqli_error($con));

$storage = array();
$hashtags_strs = array();
$points = array(array("Location", "Parent","Volume"), array("Hashtag", null, 0));
while($hashtag = mysqli_fetch_array($hashtags)){
    $points[] = array(strtolower($hashtag["hashtag"]), "Hashtag", intval($hashtag["sum_total"]));
    $hashtags_str[] = $hashtag["hashtag"];
    $storage[$hashtag["hashtag"]] = array();
}

$IN = " lower(h.hashtag) IN (' ". implode("','", $hashtags_str)."')";  

$SCHOOLS = "SELECT lower(h.hashtag) AS hashtag, h.`total`, s.`name` FROM `hashtags_by_school` AS h JOIN schools AS s ON h.school_id = s.school_id
WHERE $IN";

$hashtags = mysqli_query($con, $SCHOOLS) or die(mysqli_error($con));

while($hashtag = mysqli_fetch_array($hashtags)){
    $storage[$hashtag["hashtag"]][] = array($hashtag["name"], $hashtag["hashtag"], intval($hashtag["total"]));
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
        google.load("visualization", "1", {packages:["corechart","treemap"]});
        google.setOnLoadCallback(drawVolumeChart);
        var points = <?php echo json_encode($points); ?>;
        var tree_data;
        function drawVolumeChart() {
            tree_data = google.visualization.arrayToDataTable(points);
            var options = {
                generateTooltip: showToolTip
            };
            var tree = new google.visualization.TreeMap(document.getElementById('chart_div'));
            tree.draw(tree_data, options);
       }

        var school_data = <?php echo json_encode($storage); ?>;
       function showToolTip(row, size, value){
        var hashtag  = tree_data.getValue(row, 0);
        var num_tweets = tree_data.getValue(row, 2);

        if (hashtag == "Hashtag") {
            return '';
        }

        var num_tweets = size;
        var html = '<div style="background:#fd9; padding:10px; border-style:solid">';
        var schools = school_data[hashtag];

        schools.sort(function(a,b){
            return b[2] - a[2];
        });
        if (schools == null){
            html += 'didnt find any...';
        } else { 
            html += '<b>' + num_tweets.toString() + ' tweets by ' + schools.length.toString() + ' schools</b> <br/>';
            for (var i = 0; i < schools.length; i += 1){
                var perc = (schools[i][2] / num_tweets).toFixed(2)*100;
                var num = schools[i][2].toString();
                html += schools[i][0] + ': ' + perc + '% (' + num + ')<br/>';
            }
        }
        
        html += '</div>';
        return html;
       }

       function drawSecondOne(hashtag, data) {
            data.unshift([hashtag, null,0]);
            data.unshift(["Hashtag","Parent","Volume"]);
            var data2 = google.visualization.arrayToDataTable(data);
       }
</script>
  </head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
  </body>
</html>
