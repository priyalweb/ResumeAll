
<?php
require_once "pdo.php";
session_start();

if ( ! isset($_SESSION['name']) ) {
    die('Not logged in');
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM profile WHERE profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
    $_SESSION['success'] = 'Record deleted';
    header( 'Location: index.php' ) ;
    return;
}
if (isset($_POST['cancel'])){
  header( 'Location: index.php' ) ;
  return;
}

// Guardian: Make sure that user_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT first_name,last_name, profile_id FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

?>
<html>
<head>
  <head> <title>Priyal Dubey - Delete Profile </title>
  <?php require_once "head.php"; ?>
</head>
<body>
  <div class="container deletec">
<p>Confirm: Deleting Profile </p>
<p>First name: <?= htmlentities($row['first_name']) ?></p>
<p>Last name: <?= htmlentities($row['last_name']) ?></p>

<form method="post">
<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
<input type="submit" value="Delete" class="addbutton" name="delete">
<input type="submit" value="Cancel" name="cancel" class="cancelbutton">
</form>

</div>
</body>
<html>
