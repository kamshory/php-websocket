<?php
session_start();

// If user is already logged in, redirect to the chat page.
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("location:index.php");
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $chatroom = trim($_POST['chatroom']);

    if (!empty($username) && !empty($chatroom)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['chatroom'] = $chatroom;
        header("location:index.php");
        exit;
    } else {
        $error = "Username and Chatroom are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - PHP WebSocket Chat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-header text-center">
                    <h4>Login to Chat</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form action="login.php" method="post">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" class="form-control" autocomplete="off" autofocus>
                        </div>
                        <div class="form-group">
                            <label for="chatroom">Chatroom</label>
                            <input type="text" name="chatroom" id="chatroom" class="form-control" autocomplete="off">
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
