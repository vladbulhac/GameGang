<?php

session_start();
require_once './utils/Database.php';

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');

?><!DOCTYPE html>
<html>
<head>
<title>Game Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/gamepage-style.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=ABeeZee' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css?family=Baloo+Bhai&display=swap" rel="stylesheet">


</head>

<header>
<div class="top-menu">
   <nav>
       <ul class="nav_links">
           <li><a href="library.php?page=1&platform=none&hours=none&genre=none" style="font-size:50px;position:absolute;top:10px;left:5px;">&#8249;</li>
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
<?php
$gameid = $_GET['gameid'];
?>
 <div class="useless">
     <?php
$query = "SELECT banner_path FROM games WHERE id=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $gameid);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_array(MYSQLI_ASSOC)) { //bannerul jocului
    echo ' <img src="' . $row['banner_path'] . '" alt="game_image">';
}

?>


</div>

<div class="wrapper">

    <div class="gameinfo">

        <?php
$nr_hours = -1;
$query = "SELECT hours FROM libraries WHERE id_user=? AND id_game=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('ii', $_SESSION['userid'], $gameid);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    $nr_hours = $row['hours'];
}

$query = "SELECT title,genre,thumbnail_path FROM games WHERE id=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $gameid);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
    <div class="top-info">
    <h1>' . $row['title'] . '</h1><p>';
    echo $row['genre'];
    echo ' ';
    echo '</p>';
    echo '
    <img src="' . $row['thumbnail_path'] . '" class="game-thumbnail">
    <form action="" method="GET" class="hoursplayed submit_playtime">
    <label for="played_hours">Hours played:</label><br>
    <input type="hidden" name="gameid" value="' . $gameid . '">
    <input type="text" name="played_hours" placeholder="Number of hours...">
    <input type="text" name="first" placeholder="1st place">
    <input type="text" name="second" placeholder="2nd place">
    <input type="text" name="third" placeholder="3rd place">
    <input type="text" name="fourth" placeholder="4th place">
    <button type="submit" name="register_hours" class="button" style="top:0;left:160px;width:80px;height:35px;font-size:15px;" ><span>Confirm</span></button>
    </form>

</div>'; //formular pentru a inregistra sesiunea de joc

    if ($nr_hours > -1) { //daca nr_hours<=-1 atunci inseamna ca nu am jocul in librarie
        echo '<button type="button" name="playbtn" class="button" onclick="displayForm()"><span>PLAY</span></button>';
    } else {
        echo '  <form action="" method="POST">
        <button type="submit" name="addlibrarybtn" class="button"><span>ADD</span></button>
       </form>';
    }
}

?>
<?php
if (isset($_POST['addlibrarybtn'])) { //adaug jocul in libraria userului
    if (!isset($_SESSION['userid'])) {
        header("Location: login.php");
        exit();
    }
    $query = "SELECT id FROM libraries WHERE id_user=? AND id_game=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('ii', $_SESSION['userid'], $gameid);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $query = "INSERT INTO libraries (id_user,id_game) VALUES (?,?)";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('ii', $_SESSION['userid'], $gameid);
        $stmt->execute();
    }
}
?>
<?php
if (isset($_GET['register_hours'])) { //inregistrez numarul de ore
    $first = null;
    $second = null;
    $third = null;
    $foruth = null;
    $ok = 0;
    if (!empty($_GET['played_hours']) && $_GET['played_hours'] > 0 && $_GET['played_hours'] < 50000) {
        if (!empty($_GET['first'])) { //daca input fieldul nu e gol inseamna ca userul a mai jucat cu cineva=>inseram si in tabelul squad
            $ok = 1;
            $first = $_GET['first'];
            $query = "SELECT id FROM users WHERE username=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('s', $first);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_array(MYSQLI_ASSOC)) { //fac update la numarul de ore jucate al userului din input field
                $first_id = $row['id'];
                $query = "UPDATE libraries SET `hours`=`hours`+? WHERE id_user=? AND id_game=?";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('iii', $_GET['played_hours'], $first_id, $gameid);
                $stmt->execute();

                $place = 1;
                $query = "INSERT INTO squad (id_user1,id_user2,id_game,place) VALUES (?,?,?,?)";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('iiii', $_SESSION['userid'], $first_id, $gameid, $place);
                $stmt->execute();
            }
        }

        if (!empty($_GET['second'])) {
            $ok = 1;
            $second = $_GET['second'];
            $query = "SELECT id FROM users WHERE username=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('s', $second);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $second_id = $row['id'];

                $query = "UPDATE libraries SET `hours`=`hours`+? WHERE id_user=? AND id_game=?";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('iii', $_GET['played_hours'], $second_id, $gameid);
                $stmt->execute();

                $place = 2;
                $query = "INSERT INTO squad (id_user1,id_user2,id_game,place) VALUES (?,?,?,?)";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('iiii', $_SESSION['userid'], $second_id, $gameid, $place);
                $stmt->execute();
            }
        }

        if (!empty($_GET['third'])) {
            $ok = 1;
            $third = $_GET['third'];
            $query = "SELECT id FROM users WHERE username=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('s', $third);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $third_id = $row['id'];

                $query = "UPDATE libraries SET `hours`=`hours`+? WHERE id_user=? AND id_game=?";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('iii', $_GET['played_hours'], $third_id, $gameid);
                $stmt->execute();
                $place = 3;
                $query = "INSERT INTO squad (id_user1,id_user2,id_game,place) VALUES (?,?,?,?)";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('iiii', $_SESSION['userid'], $third_id, $gameid, $place);
                $stmt->execute();
            }
        }

        if (!empty($_GET['fourth'])) {
            $ok = 1;
            $fourth = $_GET['fourth'];
            $query = "SELECT id FROM users WHERE username=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('s', $fourth);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $fourth_id = $row['id'];

                $query = "UPDATE libraries SET `hours`=`hours`+? WHERE id_user=? AND id_game=?";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('iii', $_GET['played_hours'], $fourth_id, $gameid);
                $stmt->execute();

                $place = 4;
                $query = "INSERT INTO squad (id_user1,id_user2,id_game,place) VALUES (?,?,?,?)";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('iiii', $_SESSION['userid'], $fourth_id, $gameid, $place);
                $stmt->execute();
            }
        }

        if ($ok == 0) { //daca a fost completat numai numarul de ore atunci fac update la numarul de ore jucate numai la mine
            $query = "UPDATE libraries SET `hours`=`hours`+? WHERE id_user=? AND id_game=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('sii', $_GET['played_hours'], $_SESSION['userid'], $gameid);
            $stmt->execute();
        }
        header("Location: gamepage.php?gameid=$gameid");
        exit();
    }
}

?>


      <div class="account-related-info">
          <?php if ($nr_hours > -1) { //daca userul are jocul in librarie
    echo '
          <h2>You played this game for: <b style="color:#ff3333;">' . $nr_hours . '</b> hours</h2>';
} else {
    echo '<h2>Add this game to your library to play!</h2>';
}
?>
 <p class="friends-that-play-this">Friends that also play this game: <p>
<?php
echo ' <ul class="friends-that-play-this-list">';
$query = "SELECT u.username FROM users u JOIN friends f ON u.id=f.id_user2 JOIN libraries lib ON lib.id_user=f.id_user2 WHERE lib.id_game=? AND lib.id_user!=? AND f.id_user1=? LIMIT 5 ";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('iii', $gameid, $_SESSION['userid'], $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->num_rows;
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '<li>' . $row['username'] . '</li>';
}
if ($rows == 0) {
    echo '<li>None yet</li>';
}
echo '</ul>';
?>


        </div>


<?php
if ($nr_hours == -1) { //daca userul nu are jocul in librarie atunci arat jucatorul cu cele mai multe ore petrecute in acel joc
    echo '    <div class="top_player">
<h2 class="hr-sect" style="top:0;">Top player</h2>';
    $query = "SELECT u.username,lib.hours,u.profile_path FROM users u JOIN libraries lib ON lib.id_user=u.id WHERE lib.id_game=? ORDER BY lib.hours DESC LIMIT 1";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $gameid);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        echo ' <img src="' . $row['profile_path'] . '" alt="profile">
    <p>' . $row['username'] . '</p>
    <h2><b  style="color:#ff3333;">' . $row['hours'] . '</b> hours</h2>
    ';
    }
    echo '</div>';
}
?>
        </div>
</div>

</body>

<script type="text/javascript">

function displayForm(){
var play = document.getElementsByClassName("hoursplayed");
for(let i=0;i<play.length;i++)
play[i].style.display="block";
}


</script>


</html>