<?php
require_once "pdo.php";
require_once "util.php";
session_start();

?>


<html>
<head>
<title>Priyal Dubey - Javascript profile</title>
<?php require_once "head.php"; ?>
  <!-- <link rel="stylesheet" type="text/css" href="css/style.css"> -->
</head><body>
  <div class="container indexc">
<h1>Priyal Dubey's Resume Registry</h1>

<?php
if ( ! isset($_SESSION['name']) ) {
  $link_address3 = 'login.php';
  echo "<a id=".'loginbutton'." href='".$link_address3."'>Please log in</a> <br>";
  echo("<br>");
  $stmt = $pdo->query("SELECT first_name,last_name,headline,profile_id FROM profile");
  echo('<table>'."\n");
  echo("<tr><th>Name</th><th>Headline</th><tr>"."\n");
  while ( $profile = $stmt->fetch(PDO::FETCH_ASSOC) ) {
      echo "<tr><td>";
      // Edit</a> /
      echo('<a href="view.php?profile_id='.$profile['profile_id'].'">');
      echo(htmlentities($profile['first_name']));
      echo(" ");
      echo(htmlentities($profile['last_name']));
      echo('</a>');
      echo("</td><td>");
      echo(htmlentities($profile['headline']));
      echo("</td></tr>\n");
  }
    echo("</table>"."\n");
  // $link_address4 = 'add.php';
  // echo "<p>Attempt to <a href='".$link_address4."'>add data</a> without logging in </p>";
}  else {
          flashMessages();

          $link_address2 = 'logout.php';
          echo "<br><a id=".'logoutbutton'." href='".$link_address2."'>Logout</a>";
          echo("<br><br>");
            echo('<table >'."\n");
            echo("<tr><th>Name</th><th>Headline</th><th>Action</th><tr>"."\n");

            $stmt = $pdo->query("SELECT profile.first_name, profile.last_name, profile.headline, profile.profile_id, profile.user_id FROM users JOIN profile ON profile.user_id = users.user_id");
                // $_REQUEST['profile_id'] = $profile['profile_id'];
            while ( $profile = $stmt->fetch(PDO::FETCH_ASSOC) ) {

                // echo "<tr><td>";
                // echo('<a href="view.php?profile_id='.$profile['profile_id'].'">');
                // echo(htmlentities($profile['first_name']));
                // echo(" ");
                // echo(htmlentities($profile['last_name']));
                // echo('</a>');
                // echo("</td><td>");
                // echo(htmlentities($profile['headline']));
                // echo("</td><td>");
                if( $_SESSION['user_id'] == $profile['user_id'] ){
                  echo "<tr><td>";
                  echo('<a href="view.php?profile_id='.$profile['profile_id'].'">');
                  echo(htmlentities($profile['first_name']));
                  echo(" ");
                  echo(htmlentities($profile['last_name']));
                  echo('</a>');
                  echo("</td><td>");
                  echo(htmlentities($profile['headline']));
                  echo("</td><td>");

                echo('<a href="edit.php?profile_id='.$profile['profile_id'].'">Edit</a> / ');
                echo('<a href="delete.php?profile_id='.$profile['profile_id'].'">Delete</a>');
               }
                echo("</td></tr>\n");
            }
              echo("</table>"."\n");
          // }
}

?>
<?php
if ( isset($_SESSION['name']) && isset($_SESSION['user_id']) ) {
  $link_address = 'add.php';
  echo "<br><a class=".'addbutton'." href='".$link_address."'>Add New Entry</a>";

}
 ?>
<p class="lasttt" style="margin-top: 5%;" >Click on person's name to view details! </p>
<div>
</body>
</html>
