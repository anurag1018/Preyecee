<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="stylesheet.css" type="text/css">
</head>
<body>
<div
style="float:right; margin:10px">
<?php
// Initialize the session
session_start();
 
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  
?>

<a class="btn btn-primary" href="login.php" role="button"

>Log In</a>
<?php }
else{
?>
 <h4
 style="color:#3b5998;"
 ><span class="glyphicon glyphicon-user">&nbsp;<?php echo $_SESSION['username']; ?></span></h4>
<a class="btn btn-primary" href="logout.php" role="button"

>Log Out</a>
<?php 
}
?>


</div>

<div class="SearchBox">
    <img src="Logo.png" height="252" width="318">
<form method="POST" action="amznFkSearcher.php">
<input class="form-control" type='text' name='Keywords' size='40' placeholder="What are you looking for?" />

</form>
</div>
</body>
</html>