<?php
require_once './Controllers/Database.php';
session_start();
$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');
?><!DOCTYPE html>
<html>
<head>
<title>Register Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/register-style.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=ABeeZee' rel='stylesheet'>

</head>

<header>
<div class="top-menu">
   <nav>
       <ul class="nav_links">
           <li><a href="index.php" style="font-size:50px;position:absolute;top:10px;left:5px;">&#8249;</li>
          <li><a href="index.php">Home</a></li>
          <li><a href="leaderboard.php">Leaderboard</a></li>
          <li><a href="games.php?page=1&genre=none">Games</a></li>
          <?php
if (isset($_SESSION['userid'])) {
    echo "
          <li><a href='library.php?page=1&hours=none&genre=none'>My Library</a></li>";} else {
    echo " <li><a href='login.php'>My Library</a></li>";
}

if (!isset($_SESSION['userid'])) {echo "
          <li class='dropdown'><a href='login.php'>My Account</a>
        <div class='dropdown-content'>
            <a href='login.php'>Login</a>
            <a href='register.php'>Register</a>
        </div> </li>";} else {
    echo "  <li class='dropdown'><a href='myaccount.php?friend_page=1&notif_page=1'>" . $_SESSION['username'] . "</a>
       <div class='dropdown-content'>
        <a href='logout.php'>Log out</a>
        </div></li>";
}

?>
      </ul>
   </nav>
</div>
</header>

<body>
<div class="useless">
</div>

<div class="wrapper">
    <div class="formbox">
        <h1>Register</h1>

        <form action="" method="POST">
            <label for="accmail"><b>Email</b></label><br>
            <input type="text"  placeholder="Enter your Email address..." name="accemail" required><br>
            <label for="accuname"><b>Username</b></label><br>
            <input type="text"  placeholder="Enter username..." name="accusername" required><br>
            <label for="acctpass"><b>Password</b></label><br>
            <input type="password"  placeholder="Enter password" name="accpass" required><br>
            <button type="submit" name="registerbtn" class="button3"><span><a >Register</a></span></button>
</form>
  <?php
if (isset($_POST['registerbtn'])) {
    if (empty($_POST['accemail']) || empty($_POST['accusername']) || empty($_POST['accpass'])) {
        header("Location: register.php?registerFailed=emptyInputs");
        exit();
    } else
    if (!filter_var($_POST['accemail'], FILTER_VALIDATE_EMAIL) && !preg_match("/^[a-zA-Z0-9]*$/", $_POST['accusername'])) {
        header("Location: register.php?registerFailed=invalidInputs");
        exit();
    } else
    if (!filter_var($_POST['accemail'], FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?registerFailed=invalidEmail");
        exit();
    } else
    if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['accusername'])) {
        header("Location: register.php?registerFailed=invalidUsername");
        exit();
    } else {
        $username = $_POST['accusername'];
        $passw = $_POST['accpass'];
        $email = $_POST['accemail'];
        $stmt = $db->conn->prepare("INSERT INTO users (username,password,email) VALUES(?,?,?)");
        $stmt->bind_param('sss', $username, $passw, $email);
        $res = $stmt->execute();
        if ($res) {
            header("Location: index.php?register=success");
            exit();
        } else {
            header("Location: index.php?register=failed");
        }

        exit();
    }
}
?>
<button type="button" name="cancelbtn" class="button2"><span><a href="index.php">Cancel</a></span></button>
</div>
</div>
</body>
</html>
