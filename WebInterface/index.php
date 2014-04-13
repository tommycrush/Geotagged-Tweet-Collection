<?php
error_reporting(E_ALL);
require('db_connect.php');

$SQL = "SELECT `name`,`school_id` FROM `schools` ORDER BY `name` ASC ";
$TOP = "SELECT `name`,`school_id`,`num_tweets`, ROUND(num_tweets/students,3) AS tweets_per FROM `schools` ORDER BY num_tweets DESC LIMIT 10";
$con = connect_to_db();
$schools = mysqli_query($con, $SQL) or die(mysqli_error($con));
//$top_schools = mysqli_query($con, $TOP) or die(mysqli_error($con));
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Twitter Usage on College Campuses</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">

    <!-- Optional theme -->
    <!-- <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css"> -->
    <link href="http://getbootstrap.com/examples/jumbotron-narrow/jumbotron-narrow.css" rel="stylesheet">

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
    <script type="text/javascript">
    function format(num, fix) {
        var p = num.toFixed(fix).split(".");
        return p[0].split("").reduceRight(function(acc, num, i, orig) {
                        var pos = orig.length - i - 1
                                return  num + (pos && !(pos % 3) ? "," : "") + acc;
                                    }, "") + (p[1] ? "." + p[1] : "");
    }
    
    var startCount = -4640000; //600000;
    var fromTime = 1391982163;
    
    var d = new Date();
    var currentTime = d.getTime() / 1000;
    var per_second = 3;
    
    var currentCount = startCount + ((currentTime - fromTime) * per_second);
        
    $(document).ready(function(){
        $("#counter").text(format(currentCount));
        setInterval(function(){
            currentCount = currentCount + Math.floor(Math.random() * per_second + 2) + 0;
            $("#counter").text(format(currentCount));
        }, 1000);
    });
    </script>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <ul class="nav nav-pills pull-right">
          <li class="active"><a href="#">Home</a></li>
          <li><a href="#">About</a></li>
          <li><a href="#">Contact</a></li>
        </ul>
        <h3 class="text-muted">Twitter Research</h3>
      </div>

      <div class="jumbotron">
        <h1>Pick your school.</h1>
        <p class="lead">
            We've collected <span id="counter"></span> tweets on 
            647 college campuses in the United States. Checkout yours. 
            or <a href="#">learn more</a>.
        </p>
        <form id="pick_school" action="school.php" method="GET">
        <p>
            <select class="form-control" name="id">
                <?php
                while($school = mysqli_fetch_array($schools)){
                    echo '<option value="'.$school['school_id'].'">'.$school['name'].'</option>';
                }
                ?>
            </select>
        </p>
        </form>
        <p>
            <a class="btn btn-lg btn-success" onClick="$('#pick_school').submit();" href="#" role="button">Open School</a>
            or 
            <a class="btn btn-lg btn-success" href="rankings.php" role="button">View All Rankings</a>
        </p>
      </div>
      <div class="row marketing">
        <div class="col-lg-12">
          <h4>Wait, are these all the tweets on campus?</h4>
          <p>Ah, you got us. We can only collect tweets that are geotagged. So tweets from a computer won't be here. Sorry, it's the best we can do.</p>

          <h4>Why are you doing this?</h4>
          <p>This a research project at the <a href="http://uky.edu/">University of Kentucky</a>.</p>
        </div>

      </div>

      <div class="footer">
        <p>&copy; Company 2014</p>
      </div>
    </div> <!-- /container -->
  </body>
</html>
