<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php
    header("Location: index.php");
    return;
}


if( isset($_POST["email"]) && isset($_POST["pass"]) ){
  unset($_SESSION["name"] );

      $salt = 'XyZzy12*_';
      $stored_hash = '83699e97a7dfb2636fee4c0c12bff008';
      $check = hash('md5', $salt.$_POST['pass']);

      $stmt = $pdo->prepare('SELECT user_id, name FROM users
      WHERE email = :em AND password = :pw');

      $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ( $row !== false ) {
      $_SESSION['name'] = $row['name'];
      $_SESSION['user_id'] = $row['user_id'];
      // Redirect the browser to index.php
      header("Location: index.php");
      return;
      } else{
        error_log("Login fail ".$_POST['email']." $check");
        $_SESSION['error'] = "Incorrect password";
        header("Location: login.php");
        return;
      }
}

?>

<!DOCTYPE html>
<html>
<head>
<?php require_once "head.php"; ?>
<title>Priyal Dubey - Javascript profile</title>
</head>
<body>
<div class="container loginc">
<h1>Please Log In</h1>
<?php
flashMessages();
?>
<form method="POST">
<label for="nam">Email</label>
<input type="text" name="email" id="id_0"><br/>
<label for="id_1723">Password</label>
<!-- <input type="text" name="pass"><br/>
<input type="submit" value="Log In"> -->
<input type="password" name="pass" id="id_1723">
<br>
<input type="submit" onclick="return doValidate();" id="id_111" class="b111" value="Log In">
<input type="submit" name="cancel" id="id_222" class="b111" value="Cancel">
<!-- <input type="submit" name="cancel" value="Cancel"> -->
</form>

<script>
function doValidate() {

console.log('Validating...');

try {

pw = document.getElementById('id_1723').value;
em = document.getElementById('id_0').value;
console.log("Validating em="+em);
console.log("Validating pw="+pw);

if (pw == null || pw == "") {

alert("Both fields must be filled out");

return false;

}

else if( em.indexOf("@") == (-1) ){
  alert("Invalid email address");

  return false;
}

return true;

} catch(e) {

return false;

}

return false;

}
</script>

<!-- <a href="index.php" >Cancel</a> -->

<p style="color: #cecece;" >
Enter Email: priyaldubey2000@gmail.com or user1@gmail.com <br>
For a password hint, view source and find a password hint
in the HTML comments.
<!-- Hint: The password is the nine character sport game
which I(:P), Saina Nehwal, PV Sindhu plays!. -->
</p>
</div>
</body>
