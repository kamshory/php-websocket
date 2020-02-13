<meta charset='UTF-8' />
<?php
session_start();
$post = $_POST;

if (isset($post['submit'])){
		$username = trim($post['username']);
	if (!empty($username )) {
		$_SESSION['logged_in'] = true;
		$_SESSION['username'] = $username;
		header("location:index.php");	
	} 
	else 
	{
		echo "You must enter username"; return false;
	}

}
?>



<form ation="" method="post">
	Username : <input name="username" type="text"> <br>
	<input type="submit" value="Login" name="submit">
</form>
