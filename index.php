<?php session_start();
?>
<?php
if (! $_SESSION['logged_in']) {
	header("location:login.php");
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8' />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="username" content="<?php echo $_SESSION['username'];?>">
<title>PHP WebSocket Chat</title>
<!-- Bootstrap 4 CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- Custom CSS for chat box -->
<style>
	#message_box {
		height: 400px;
		overflow-y: scroll;
	}
</style>
</head>
<body>

<div class="container mt-4">
	<div class="card">
		<div class="card-header">PHP WebSocket Chat - Welcome <span class="font-weight-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span></div>
		<div class="card-body" id="message_box">
			<!-- Messages will be appended here by JavaScript -->
		</div>
		<div class="card-footer">
			<div class="input-group">
				<input type="text" name="message" id="message" placeholder="Type your message..." class="form-control" />
				<div class="input-group-append">
					<button id="send-btn" class="btn btn-primary">Send</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script language="javascript" type="text/javascript" src="js/script.js"></script>

</body>
</html>
