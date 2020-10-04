<?php
require_once './Controllers/Database.php';

session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');
?><!DOCTYPE html>
<html>
<head>
<title>Profile Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/profilepage-style.css" rel="stylesheet" type="text/css">
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

<div class="acc-info" id="profile">
  <?php
$user_id = $_GET['id'];

$query = "SELECT username,email,register_date,profile_path,acc_type,session_type FROM users WHERE id=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_array(MYSQLI_ASSOC)) { //informatiile utilizatorului,imaginea de profil,nume,email,etc
    $user_name = $row['username'];
    $user_session_type = $row['session_type'];
    echo '<img src="' . $row['profile_path'] . '" alt="no_profile_pic_selected" class="acc-profile-img">
    <h2 class="acc-name">' . $row['username'] . '</h2>
    <p class="acc-type">' . $row['acc_type'] . '</p>
    <p class="acc-registr-date">' . $row['register_date'] . '</p>
    <p class="acc-email">' . $row['email'] . '</p> ';
}

$query = "SELECT id  FROM friends WHERE id_user1=? AND id_user2=? ";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('ii', $_SESSION['userid'], $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    echo ' <button type="button" name="removefbtn" class="button2"><span><a href="removefriend.php?id=' . $user_id . '">Remove friend</a></span></button>';
} else {
    echo '<button type="button" name="addfbtn" class="button2" style="left:770px;"><span><a href="addfriend.php?id=' . $user_id . '">Add friend</a></span></button>';
}

if ($user_session_type == 'private' && $res->num_rows <= 0) {
    echo '<h1 style="color:white;left:60px;position:absolute;top:400px;">This account is private, add as a friend to see its profile</h1>';
} else {
    $query = "SELECT g.title,g.thumbnail_path,lib.hours FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? ORDER BY lib.hours DESC LIMIT 1";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    echo ' <h2 class="hr-sect">' . $user_name . ' stats</h2>';
    if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        echo '
<img src="' . $row['thumbnail_path'] . '" class="mostplayed-game">
<p class="mostplayed-title">' . $row['title'] . '</p>
<p class="mostplayed-hours"><b>' . $row['hours'] . '</b> hours</p>';
    }

    $query = "SELECT g.title,g.thumbnail_path,lib.hours FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? ORDER BY lib.hours ASC LIMIT 1";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        echo '
<img src="' . $row['thumbnail_path'] . '" class="leastplayed-game">
<p class="leastplayed-title">' . $row['title'] . '</p>
<p class="leastplayed-hours"><b>' . $row['hours'] . '</b> hours</p>';
    }

    $query = "SELECT SUM(hours) AS sum FROM libraries WHERE id_user=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        echo '<p class="total-nr-hrs">Time played accross all games: <b>' . $row['sum'] . '</b> hours</p>';
    }

    $query = "SELECT id  FROM friends WHERE id_user1=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    echo '<p class="total-nr-friends">' . $user_name . ' has <b>' . $res->num_rows . '</b> friends</p>';

    $query = "SELECT SUM(score) AS score FROM trivia_score WHERE id_user=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        if ($row['score'] == null) {$row['score'] = 0;}
        echo '
    <p class="total-nr-points">' . $user_name . ' has <b>' . $row['score'] . '</b> trivia points</p>
    ';
    }

    $query = "SELECT g.genre,COUNT(lib.id_game) AS count FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? GROUP BY g.genre ORDER BY 2 DESC";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    echo '<p class="games-by-genre">Games by genre</p>
<ul class="list-of-genres">';
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        echo '<li><p>' . $row['genre'] . ':<b>' . $row['count'] . '</b> games</p></li>';
    }
    echo '</ul>';

    $query = "SELECT s.played_at,s.place,g.title FROM squad s JOIN games g ON g.id=s.id_game WHERE  s.id_user2=? ORDER BY played_at DESC LIMIT 1";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->store_result();
    if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        echo '
<div class="squad-play">
      <p style="float:left;">Last squad play:</p>
      <p style="float:left;margin-left:10px;">' . $row['played_at'] . '</p>
      <p style="float:left;margin-left:10px;">' . $user_name . ' place was ' . $row['place'] . ' in the leaderboard and played ' . $row['title'] . ' with:
';
        $played = '' . $row['played_at'] . '';
        $query = "SELECT u.username FROM squad s JOIN users u ON u.id=s.id_user1 WHERE s.id_user1!=? AND s.id_user2=? AND played_at=? UNION ALL SELECT u.username FROM squad s JOIN users u ON u.id=s.id_user2 WHERE s.id_user2!=? AND s.id_user1=? AND played_at=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('iisiis', $user_id, $user_id, $played, $user_id, $user_id, $played);
        $stmt->execute();
        $result = $stmt->get_result();

        $ok_squad = 0;
        while ($row_squad = $result->fetch_array(MYSQLI_ASSOC)) {
            $ok_squad = 1;
            echo $row_squad['username'];
            echo ' ';
        }
        if ($ok_squad == 0) {
            echo 'No one';
        }
        echo '</p></div>';
    }
}
?>





</div>

</div>
</body>

<footer>
<h1 class="hr-sect2"><?php echo $user_name; ?> plays</h1>

<div class="suggestions">
  <?php

$query = "SELECT g.title,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? LIMIT 9";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();

$counter = 1;
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    if ($counter == 1) {
        echo '<div class="friendsgame fade flexboxfriends">';
    }
    echo '<div >
<img src="' . $row['thumbnail_path'] . '" alt="game_image">
<div class="whatgame">' . $row['title'] . '</div>
</div>';
    if ($counter == 3) {
        $counter = 0;
        echo '</div>';
    }
    $counter++;
}
if ($counter < 3) {
    echo '</div>';
} //jocuri pe care le are utilizatorul in librarie

?>
</div>
<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
 <a class="next" onclick="plusSlides(1)">&#10095;</a>
</footer>

<script>
var slideIndex=1;
showSlides(slideIndex);

function plusSlides(n){
    showSlides(slideIndex+=n);
}

function showSlides(n){
    var i;
    var slides = document.getElementsByClassName("friendsgame");
    if(n>slides.length){slideIndex=1;}
    if(n<1) {slideIndex=slides.length;}
    for(i=0;i<slides.length;i++)
    {
        slides[i].style.display="none";
    }
    slides[slideIndex-1].style.display="block";
}


</script>

</html>
