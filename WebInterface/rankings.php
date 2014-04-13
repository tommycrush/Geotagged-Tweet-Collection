<?php
error_reporting(E_ALL);
require('db_connect.php');

$TOP_TWEETS = "SELECT * FROM `schools` ORDER BY num_tweets DESC";
$TOP_PER_STUDENT = "SELECT *, ROUND(num_tweets/students,3) AS tweets_per FROM `schools` ORDER BY tweets_per DESC";
$USERS_PER_STUDENT = "SELECT *, ROUND(num_twitter_users/students,3) AS users_per_student FROM `schools` ORDER BY users_per_student DESC";

$con = connect_to_db();

// @todo: use mysqli_multi_query to reduce network trips
$tweets = mysqli_query($con, $TOP_TWEETS) or die(mysqli_error($con));
$per_student = mysqli_query($con, $TOP_PER_STUDENT) or die(mysqli_error($con));
$users_per_student = mysqli_query($con, $USERS_PER_STUDENT) or die(mysqli_error($con));
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Rankings of Twitter Usage on College Campuses</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link href="http://getbootstrap.com/examples/jumbotron-narrow/jumbotron-narrow.css" rel="stylesheet">

    <style type="text/css">
        .tab-content > div {
            padding-top:30px;
        }
    </style>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
    
    <link href="//datatables.net/download/build/nightly/jquery.dataTables.css" rel="stylesheet" type="text/css" />
    <script src="//datatables.net/download/build/nightly/jquery.dataTables.js"></script>
    <script type="text/javascript">
    $(document).ready( function () {
        $(".tab-pane").each(function(index){
            var id = $(this).attr('id') + "_data_table";
            $("table#" + id).DataTable();
        });
        
        $('a[data-toggle="tab"]').click(function (e) {
              e.preventDefault();
                $(this).tab('show');
        });
    });
    </script>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <ul class="nav nav-pills pull-right">
          <li class=""><a href="index.php">Home</a></li>
          <li class="active"><a href="#">Rankings</a></li>
          <li><a href="about.php">Contact</a></li>
        </ul>
        <h3 class="text-muted">Twitter Research | Rankings</h3>
      </div>

      <div class="jumbotron">
        <h1>And the winner is...</h1>
        <p class="lead">
            Well, that depends on how you measure. Total tweets? Tweets per student? Remove outliers?
            Take a look for yourself.
        </p>
      </div>

        <!-- Nav tabs -->
        <ul class="nav nav-tabs">
            <li class="active"><a href="#num_tweets" data-toggle="tab"># of Tweets</a></li>
            <li><a href="#tweets_per_student" data-toggle="tab">Tweets/Student</a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane active" id="num_tweets">
                <div class="alert alert-info">
                    This one is simple: The total # of tweets that have occured between X and now on each campus.
                </div>
                 <table id="num_tweets_data_table" class="table table-striped">
                    <thead>
                        <tr><td>#</td><td>School</td><td># of tweets</td></tr>
                    </thead>
                    <tfoot>
                        <tr><td>#</td><td>School</td><td># of tweets</td></tr>
                    </tfoot>
                    <tbody>
                    <?php
                        $i = 1;
                        while($row = mysqli_fetch_array($tweets)){
                            echo '<tr><td>'.$i.'</td><td><a href="school.php?id='.$row['school_id'].'" target="_blank">'.
                                $row['name'].'</a></td><td>'.$row['num_tweets'].'</td></tr>';
                            $i += 1;
                        }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane" id="tweets_per_student">
                <div class="alert alert-info">
                    Total number of tweets divided by the number of students attending the school. 
                </div>
                <table id="tweets_per_student_data_table" class="table table-striped">
                    <thead>
                        <tr><td>#</td><td>School</td><td># of tweets per student</td></tr>
                    </thead>
                    <tfoot>
                        <tr><td>#</td><td>School</td><td># of tweets per student</td></tr>
                    </tfoot>
                    <tbody>
                    <?php
                        $i = 1;
                        while($row = mysqli_fetch_array($per_student)){
                            echo '<tr><td>'.$i.'</td><td><a href="school.php?id='.$row['school_id'].'" target="_blank">'.
                                $row['name'].'</a></td><td>'.$row['tweets_per'].'</td></tr>';
                            $i += 1;
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div><!-- /tab-content -->
      <div class="footer">
        <p>&copy; Company 2014</p>
      </div>
    </div> <!-- /container -->
  </body>
</html>
