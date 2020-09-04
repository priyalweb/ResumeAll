<?php
//make the database connection and leave it in the variable $pdo
require_once 'pdo.php';
require_once 'util.php';

session_start();

// if the user is not logged in redirect back to index.php
//with an error
if ( ! isset($_SESSION['user_id']) ){
    die('ACCESS DENIED');
    return;
}

//if the user requested cancel go back to index.php
if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php
    header('Location: index.php');
    return;
}

//make sure the REQUEST parameter is present
if ( ! isset($_REQUEST['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

//load up the profile in question
$stmt = $pdo->prepare('SELECT * FROM Profile
  WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array(":prof" => $_REQUEST['profile_id'],
       ':uid' => $_SESSION['user_id'] ));                       //user id check to see if no one else can alter profile, just by providing profile idea

$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ($profile === false){
    $_SESSION['error'] = 'Could not load profile';
    header( 'Location: index.php' ) ;
    return;
}

//handle the incoming data
if ( isset($_POST['first_name']) && isset($_POST['last_name'])
     && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) ) {

    // Data validation
    $msg = validateProfile();
    if ( is_string($msg) ){
      $_SESSION['error'] = $msg;
      header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
      return;
    }

    $msg = validatePos();
    if ( is_string($msg) ){
      $_SESSION['error'] = $msg;
      header('Location: edit.php?profile_id='.$_REQUEST["profile_id"]);
      return;
    }

    $msg2 = validateEdu();
    if ( is_string($msg2) ){
      $_SESSION['error'] = $msg2;
      header('Location: edit.php?profile_id='.$_REQUEST["profile_id"]);
      return;
    }

    //begin the update the date
    $sql = 'UPDATE Profile SET first_name = :fn,
            last_name = :ln, email = :em, headline = :he, summary = :su
            WHERE profile_id = :pid AND user_id= :uid';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
      ':pid' => $_REQUEST['profile_id'],
      ':uid' => $_SESSION['user_id'],
      ':fn' => $_POST['first_name'],
      ':ln' => $_POST['last_name'],
      ':em' => $_POST['email'],
      ':he' => $_POST['headline'],
      ':su' => $_POST['summary'])
      );

      //clear out the old position enteries
       $stmt = $pdo->prepare('DELETE FROM Position
       WHERE profile_id=:pid');
       $stmt->execute(array(':pid'=> $_REQUEST['profile_id']));

       //insert the position enteries
       insertPositions($pdo, $_REQUEST['profile_id']);

       //clear out the old education enteries
       $stmt = $pdo->prepare('DELETE FROM Education
              WHERE profile_id= :pid');
       $stmt ->execute(array(':pid' => $_REQUEST['profile_id']));

       //insert the education enteries
        insertEducations($pdo, $_REQUEST['profile_id']);

        $_SESSION['success'] = 'Profile updated';
        header("Location: index.php");
        return;
}
//load up the position and education rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>
<!DOCTYPE html>
<html>
<head> <title>Priyal Dubey Profile Edit</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container editc">
  <h1>Editing profile for <?php echo(htmlentities($_SESSION['name'])); ?> </h1>
  <?php flashMessages(); ?>
<?php
$m = htmlentities($profile['first_name']);
$mm = htmlentities($profile['last_name']);
$y = htmlentities($profile['email']);
$mmm = htmlentities($profile['headline']);
$ss = htmlentities($profile['summary']);
//$profile_id = $profile['profile_id'];
?>

<form method="post" action="edit.php">
  <input type="hidden" name="profile_id"
  value="<?= htmlentities($_REQUEST['profile_id']); ?>"/>
   <p>First Name:
   <input type="text" name="first_name" class="fn" size="60"
   value="<?= $m ?>"></p>
   <p>Last Name:
   <input type="text" name="last_name" class="ln" size="60"
   value="<?= $mm ?>"></p>
   <p>Email:
   <input type="text" name="email" class="em" size="30"
   value="<?= $y ?>"></p>
   <p>Headline:<br/>
   <input type="text" name="headline" class="he" size="80"
   value="<?= $mmm ?>"></p>
   <p>Summary:<br/>
   <textarea name="summary" rows="8" cols="80" class="sm"><?= $ss ?></textarea></p>

<?php
$countEdu = 0;

echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo('<div id="edu_fields">'."\n");
if ( count($schools) >0 ){
  foreach ($schools as $school ) {
    $countEdu++;
    echo('<div id="edu'.$countEdu.'">');
    echo('
<p>Year: <input type="text" class="ye" name="edu_year'.$countEdu.'" value="'.$school['year'].'" />
<input type="button" value="-" class="minus" onclick="$(\'#edu'.$countEdu.'\').remove(); return false;"></p>
<p>School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school"
value="'.htmlentities($school['name']).'" />');
    echo("\n</div>\n");
  }
}
echo("</div></p>\n");
?>

   <?php
   $countPos =0;

   echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
   echo('<div id="position_fields">'."\n");
   if( count($positions) > 0){
     foreach($positions as $position ){
       $countPos++;
       echo('<div class="position" id="position'.$countPos.'">'."\n");
       echo('
       <p>Year: <input type="text" class="ye" name="year'.$countPos.'"
       value="'.htmlentities($position['year']).'" />
       <input type="button" class="minus" value ="-"
       onclick =" $(\'#position'.$countPos.'\').remove(); return false;"><br>');

       echo(' <textarea name="desc'.$countPos.'" class="desc" rows="8" cols="80">'."\n");
       echo(htmlentities($position['description'])."\n");
       echo("\n</textarea>\n</div>\n");
     }

   }
   echo("</div></p>\n");

   ?>

   <p>
   <input type="submit" class="addbutton" value="Save">
   <input type="submit" class="cancelbutton" name="cancel" value="Cancel">
   </p>
 </form>
 <!-- <script src="js/jquery-1.10.2.js"></script>
 <script src="js/jquery-ui-1.11.4.js"></script> -->
 <script>
 countPos = <?= $countPos ?>;
 countEdu = <?= $countEdu ?>;

 //https://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
 $(document).ready(function(){
     window.console && console.log('Document ready called');
     $('#addPos').click(function(event){
       //http://api.jquery.com/event.preventdefault/
       event.preventDefault();
       if(countPos >=9){
         alert("Maximum of nine position enteries exceeded");
         return;
       }
       countPos++;
       window.console && console.log('Adding position '+countPos);
       $('#position_fields').append(
         '<div  id="position'+countPos+'"> \
         <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
         <input type="button" value="-" \
             onclick =" $(\'#position'+countPos+'\').remove(); return false;"> </p> \
         <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea> \
          </div>');
     });

     $('#addEdu').click(function(event){
       event.preventDefault();
       if( countEdu >= 9 ){
         alert("Maximum of nine education enteries exceeded");
         return;
       }
       countEdu++;
       window.console && console.log("adding education "+countEdu);

       //grab some html with hot spots and insert into DOM
       var source = $("#edu-template").html();
       $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

       //add the evem handler to the new ones
       $('.school').autocomplete({
         source: "school.php"
       });
     });

     $('.school').autocomplete({
       source: "school.php"
     });

 });

 </script>
 <!-- HTML with substitution hot spots -->
 <script id="edu-template" type="text">
    <div id="edu@COUNT@">
      <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
      <input type="button" value="-" onclick="$('#edu@COUNT@').remove(); return false;"><br>
      <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
      </p>
    <div>
 </script>
 </div>
 </body>
 </html>
