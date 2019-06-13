<?php
session_start();
require_once './Controllers/database.php';

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');
?><!DOCTYPE html>
<html>
<head>
<title>Login Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/login-style.css" rel="stylesheet" type="text/css">
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
          <li class='dropdown'><a href='login.php'>My account</a>
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
         <h1>Login</h1>
         <form action="" method="POST">
             <label for="accname"><b>Email or Username</b></label><br>
             <input type="text" placeholder="Enter Email or Username..." name="accname" required>
             <br>
             <label for="accpass"><b>Password</b></label><br>
             <input type="password" placeholder="Enter Password..." name="accpass" required><br>

             <button type="submit" name="loginbtn" class="button1"><span>Login</span></button>


         </form>
         <?php
if (isset($_POST['loginbtn'])) {
    if (empty($_POST['accname']) || empty($_POST['accpass'])) {
        header("Location: login.php?=loginFailed_FieldsLeftEmpty");
        exit();
    } else {
        if (preg_match("/@/i", $_POST['accname'])) {$email = $_POST['accname'];
            $username = null;} else {
            $username = $_POST['accname'];
            $email = null;
        }
        $passw = $_POST['accpass'];
        $stmt = $db->conn->prepare("SELECT id,username,acc_type,session_type FROM users WHERE (username=? AND password=?) OR (email=? AND password=?)");
        $stmt->bind_param('ssss', $username, $passw, $email, $passw);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $_SESSION['userid'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['acc_type'] = $row['acc_type'];
            $_SESSION['session_type'] = $row['session_type'];
            header("Location: index.php?login=success");
        } else {
            header("Location: login.php?login=Failed_noSuchUser");
            exit();
        }
    }
}
?>
         <button type="submit" name="cancelbtn" class="button2"><span><a href="index.php">Cancel</a></span></button>
         <p>Don't have an account?</p>
         <button type="submit" name="registerbtn" class="button3"><span><a href="register.php" style="color:#24252A;">Register</a></span></button>
    </div>
</div>

</body>

</html>
