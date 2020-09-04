<?php // line 1 added to enable color highlight
//make the database connection and leave it in the variable $pdo
require_once "pdo.php";
require_once "util.php";
session_start();

//if the user is not loggin in redirect back to index.php
//with an error
if ( ! isset($_SESSION['user_id']) ) {
    die('ACCESS DENIED');
    return;
}
//if the user requested cancel go back to index.php
if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php
    header("Location: index.php");
    return;
}

//handle the incoming data
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email'])
     && isset($_POST['headline']) && isset($_POST['summary'])  ) {

             $msg = validateProfile();
             if ( is_string($msg) ){
               $_SESSION['error'] = $msg;
               header("Location: add.php");
               return;
             }

             //VALIDATE POSITION ENETERIES
               $msg = validatePos();
               if ( is_string($msg) ){
                 $_SESSION['error'] = $msg;
                 header("Location: add.php");
                 return;
               }

               $msg2 = validateEdu();
               if ( is_string($msg2) ){
                 $_SESSION['error'] = $msg2;
                 header('Location: add.php');
                 return;
               }

                    //data is valid-time to insert

                    $stmt = $pdo->prepare('INSERT INTO Profile
                      (user_id, first_name, last_name, email, headline, summary)
                      VALUES ( :uid, :fn, :ln, :em, :he, :su)');

                    $stmt->execute(array(
                      ':uid' => $_SESSION['user_id'],
                      ':fn' => $_POST['first_name'],
                      ':ln' => $_POST['last_name'],
                      ':em' => $_POST['email'],
                      ':he' => $_POST['headline'],
                      ':su' => $_POST['summary'])
                      );

                      //to give the id for which we just added the details:
                      $profile_id = $pdo-> lastInsertId();

                      //INSERT THE POSITION ENETERIES
                      $rank = 1;
                      for($i=1; $i<=9 ; $i++){
                        if(!isset($_POST['year'.$i]) ) continue;
                        if(!isset($_POST['desc'.$i]) ) continue;
                        $year = $_POST['year'.$i];
                        $desc = $_POST['desc'.$i];


                        $stmt = $pdo->prepare('INSERT INTO Position
                          (profile_id, rank, year, description)
                          VALUES ( :pid, :rank, :year, :desc)');
//$profile_id is forieng key
                        $stmt->execute(array(
                            ':pid' => $profile_id,
                            ':rank' => $rank,
                            ':year' => $year,
                            ':desc' => $desc)
                        );
                        $rank++;
                      }

                      $rank = 1;
                      for($i=1; $i<=9; $i++){
                        if ( !isset($_POST['edu_year'.$i]) ) continue;
                        if ( !isset($_POST['edu_school'.$i]) ) continue;
                        $year = $_POST['edu_year'.$i];
                        $school = $_POST['edu_school'.$i];

                        //lookup the school if it is there.
                        $institution_id = false;
                        $stmt = $pdo->prepare('SELECT institution_id FROM
                              Institution WHERE name = :name');
                        $stmt->execute(array(':name' => $school));
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ( $row !== false ) $institution_id = $row['institution_id'];

                        //if there was no institution. insert it
                        if ( $institution_id === false ) {
                          $stmt = $pdo->prepare('INSERT INTO Institution
                                (name) VALUES (:name)');
                          $stmt->execute(array(':name' => $school));
                          $institution_id = $pdo->lastInsertId();
                        }

                        $stmt = $pdo->prepare('INSERT INTO Education
                            (profile_id, rank, year, institution_id)
                            VALUES (:pid, :rank, :year, :iid)');
                        $stmt->execute(array(
                            ':pid' => $profile_id,
                            ':rank' => $rank,
                            ':year' => $year,
                            ':iid' => $institution_id)
                        );
                        $rank++;

                      }


                      $_SESSION['success'] = "Profile added";
                      header("Location: index.php");
                      return;

}
?>

<!DOCTYPE html>
<html>
<head> <title>Priyal Dubey - Profile Add </title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container addc">
<h1>Adding profile for <?php echo(htmlentities($_SESSION['name'])); ?> </h1>
<?php flashMessages(); ?>


<form method="post">
  <p>First Name:
  <input type="text" name="first_name" class="fn" size="60"/></p>
  <p>Last Name:
  <input type="text" name="last_name" class="ln" size="60"/></p>
  <p>Email:
  <input type="text" name="email" class="em" size="30"/></p>
  <p>Headline:<br/>
  <input type="text" name="headline" class="he" size="80"/></p>
  <p>Summary:<br/>
  <textarea name="summary" rows="8" class="sm" cols="80"></textarea></p>
  <p>Education:
  <input type="submit" id="addEdu" class="ed" value="+">
  <div id="edu_fields">
  </div>
  </p>
  <p>Position:
  <input type="submit" id="addPos" class="po"  value="+">
  <div id="position_fields">
  </div>
  </p>
  <p>
  <input type="submit" class="addbutton" value="Add">
  <input type="submit" name="cancel" class="cancelbutton" value="Cancel">
  </p>
</form>

<script>
countPos = 0;  //global funct. in js
countEdu = 0;
//https://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
  // window.console &&
  console.log('Document ready called');
  $('#addPos').click(function(event){
    //http://api.jquery.com/event.preventdefault/
    event.preventDefault();  //like return false in old school js
    if(countPos >=9){
      alert("Maximum of nine position enteries exceeded");
      return;
    }
    countPos++;
    window.console && console.log("Adding position"+countPos);
    $('#position_fields').append(
      '<div  id="position'+countPos+'"> \
      <p>Year: <input type="text" class="ye" name="year'+countPos+'" value="" /> \
      <input type="button" class="minus" value="-" \
          onclick ="$(\'#position'+countPos+'\').remove(); return false;"></p> \
      <textarea  name="desc'+countPos+'" class="desc" rows="8" cols="80"></textarea> \
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
      <p>Year: <input type="text" class="ye" name="edu_year@COUNT@" value="" />
      <input type="button" class="minus" value="-" onclick="$('#edu@COUNT@').remove(); return false;"><br>
      <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
      </p>
    <div>
 </script>
</div>
</body>
</html>
