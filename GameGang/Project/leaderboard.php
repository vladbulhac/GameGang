<?php
require_once './utils/Database.php';

session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');
?><!DOCTYPE html>
<html>
<head>
<title>Leaderboard Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/leaderboard-style.css" rel="stylesheet" type="text/css">
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

<div class="leaderboards">
<div style="position:relative;top:50px;color:white;text-align:center;"><h2>TOP PLAYERS</h2></div>
<div class="top-trivia">
<h2 class="hr-sect">Top 3 players with most trivia points</h2>

<?php
$query = "SELECT u.profile_path,u.username,SUM(ts.score) AS sum FROM trivia_score ts JOIN users u ON u.id=ts.id_user GROUP BY u.username ORDER BY 3 DESC LIMIT 1";
$res = $db->conn->query($query);
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {

    echo '
<div class="middle-trivia" style="text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:white;">
<h1 style="color:#ff3333;">1st</h1>
<img src="' . $row['profile_path'] . '" alt="profile_top1" style="width:150px;height:100px;">
   <p>' . $row['username'] . '</p>
   <div class="bars"  id="bar-middle" style="text-align:center;color:white;max-height:330px;"><p>' . $row['sum'] . ' points</p> </div>
</div>
';
} //userul cu cele mai multe puncte obtinute la trivia

$query = "SELECT u.profile_path,u.username,SUM(ts.score) AS sum FROM trivia_score ts JOIN users u ON u.id=ts.id_user GROUP BY u.username ORDER BY 3 DESC LIMIT 1,1";
$res = $db->conn->query($query);
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
<div class="left-trivia" style="text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:white;">
<h2 style="color:green;">2nd</h2>
<img src="' . $row['profile_path'] . '" alt="profile_top2" style="width:150px;height:100px;">
<p>' . $row['username'] . '</p>
<div class="bars" id="bar-left" style="text-align:center;color:white;background-color:green;max-height:290px;"><p>' . $row['sum'] . ' points</p> </div>
</div>
';
} //al doilea user cu cele mai multe puncte obtinute la trivia

$query = "SELECT u.profile_path,u.username,SUM(ts.score) AS sum FROM trivia_score ts JOIN users u ON u.id=ts.id_user GROUP BY u.username ORDER BY 3 DESC LIMIT 2,1";
$res = $db->conn->query($query);
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
<div class="right-trivia" style="text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:white;">
<h3 style="color:blue;">3rd</h3>
<img src="' . $row['profile_path'] . '" alt="profile_top2" style="width:150px;height:100px;">
<p>' . $row['username'] . '</p>
<div class="bars" id="bar-right" style="text-align:center;color:white;background-color:blue;max-height:250px;"><p>' . $row['sum'] . ' points</p> </div>
</div>
';
} //al treilea user cu cele mai multe puncte obtinute la trivia
?>





</div>

<div class="top-played">
<h2 class="hr-sect">Top 3 players with most play time</h2>

<?php
$query = "SELECT u.profile_path,u.username,SUM(lib.hours) AS sum FROM libraries lib JOIN users u ON u.id=lib.id_user GROUP BY u.username ORDER BY 3 DESC LIMIT 1";
$res = $db->conn->query($query);
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
<div class="middle-played" style="text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:white;">
<h1 style="color:#ff3333;">1st</h1>
<img src="' . $row['profile_path'] . '" alt="profile_top1" style="width:150px;height:100px;">
   <p>' . $row['username'] . '</p>
   <div class="bars"  id="barp-middle" style="text-align:center;color:white;max-height:330px;"><p>' . $row['sum'] . ' hours</p> </div>
</div>';
} //jucatorul cu cele mai multe ore petrecute in jocuri

$query = "SELECT u.profile_path,u.username,SUM(lib.hours) AS sum FROM libraries lib JOIN users u ON u.id=lib.id_user GROUP BY u.username ORDER BY 3 DESC LIMIT 1,1";
$res = $db->conn->query($query);
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
<div class="left-played" style="text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:white;">
<h2 style="color:green;">2nd</h2>
<img src="' . $row['profile_path'] . '" alt="profile_top2" style="width:150px;height:100px;">
<p>' . $row['username'] . '</p>
<div class="bars" id="barp-left" style="text-align:center;color:white;background-color:green;max-height:290px;"><p>' . $row['sum'] . ' hours</p> </div>
</div>
';
} //al doilea jucator cu cele mai multe ore petrecute in jocuri

$query = "SELECT u.profile_path,u.username,SUM(lib.hours) AS sum FROM libraries lib JOIN users u ON u.id=lib.id_user GROUP BY u.username ORDER BY 3 DESC LIMIT 2,1";
$res = $db->conn->query($query);
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
<div class="right-played" style="text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:white;">
<h3 style="color:blue;">3rd</h3>
<img src="' . $row['profile_path'] . '" alt="profile_top2" style="width:150px;height:100px;">
<p>' . $row['username'] . '</p>
<div class="bars" id="barp-right" style="text-align:center;color:white;background-color:blue;max-height:250px;"><p>' . $row['sum'] . ' hours</p> </div>
</div>
';
} //userul de pe locul trei
?>


</div>





</div>
</div>
</body>

<script>

    function moveBars(barId){
        var elem=document.getElementById(barId);
        var height=1;
        var id=setInterval(frame,10);
        function frame(){
            if(height>=100){
                clearInterval(id);
            }else{
                height++;
                elem.style.height=height+'%';
            }
        }
    }
moveBars("bar-middle");
moveBars("bar-left");
moveBars("bar-right");

moveBars("barp-middle");
moveBars("barp-left");
moveBars("barp-right");
    </script>


</html>