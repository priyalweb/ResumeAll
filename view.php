<?php
require_once "pdo.php";
require_once 'util.php';
session_start();


// Guardian: first_name sure that profile_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);
?>

<!DOCTYPE html>
<html>
<head>
<title>Priyal Dubey's Profile View</title>
<?php require_once "head.php"; ?>
</head>
<body>
<?php
// Flash pattern
flashMessages();
?>

<div class="container viewc">
<h1>Profile information</h1>
<?php
$m = htmlentities($row['first_name']);
$mm = htmlentities($row['last_name']);
$y = htmlentities($row['email']);
$mmm = htmlentities($row['headline']);
$ss = htmlentities($row['summary']);
$profile_id = $row['profile_id'];

?>
<p>First Name:
<?=$m ?></p>
<p>Last Name:
<?=$mm ?></p>
<p>Email:
<?=$y ?></p>
<p>Headline:<br/>
<?=$mmm ?></p>
<p>Summary:<br/>
<?=$ss ?>
</p>
<p>Education:
  <?php
  $countEdu =0;
  echo('<ul>');
  foreach($schools as $school ){
    $countEdu++;
    echo('<li>');
    echo(htmlentities($school['year']) );
    echo(": ");
    echo(htmlentities($school['name']) );
  }
  echo('</li></ul>');
  ?>
</p>
<p>Position:
  <?php
  $countPos =0;
  echo('<ul>');
  foreach($positions as $position ){
    $countPos++;
    echo('<li>');
    echo(htmlentities($position['year']) );
    echo(": ");
    echo(htmlentities($position['description']));
  }
  echo('</li></ul>');
  ?>
</p>
<a class="addbutton" href="index.php">Done</a>
</div>
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script></body>
</html>
