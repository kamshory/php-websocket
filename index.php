<?php 
session_start();

if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("location:login.php");
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
	header("location:login.php");
	exit;
}

$schema = "ws";
$hostName = $_SERVER['HTTP_HOST'];
if(strpos($hostName, ":") !== false)
{
	$arr = explode(":", $hostName);
	$hostName = $arr[0];
}
$wsPortNumber = 8889;
$host = sprintf("%s:%s", $hostName, $wsPortNumber);
$path = "/chat";
$wsServerUrl = sprintf("%s://%s%s", $schema, $host, $path);



?>
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8' />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="username" content="<?php echo $_SESSION['username'];?>">
<meta name="chatroom" content="<?php echo $_SESSION['chatroom']; ?>" />
<meta name="websocket" content="<?php echo $wsServerUrl; ?>" />
<title>PHP WebSocket Chat</title>
<!-- Bootstrap 4 CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- Custom CSS for chat box -->
 <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<span>Room: <span class="badge badge-info mr-2"><?php echo htmlspecialchars($_SESSION['chatroom']); ?></span> Welcome <span class="font-weight-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
			<a href="index.php?logout=true" class="btn btn-sm btn-outline-secondary">Logout</a>
		</div>
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

<!-- Bootstrap Modal for Alerts -->
<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="alertModalLabel">Notification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="alertModalBody">
        <!-- Alert message will be inserted here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script language="javascript" type="text/javascript" src="js/script.js"></script>

</body>
</html>
