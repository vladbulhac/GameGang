<?php
require_once './Controllers/Database.php';
require_once './Controllers/TriviaGenerator.php';

session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');
?><!DOCTYPE html>
<html>
<head>
<title>Main Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/mainpage-style.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=ABeeZee' rel='stylesheet'>


</head>

<header>
<div class="top-menu">
   <nav>
       <ul class="nav_links">
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
         <li> <form action="search.php?" method="GET">
             <input type="hidden" name="page" value="1">
             <input type="text" name="search" placeholder="I am looking for..." style="width:300px">
             <button type="submit">Search</button>
         </form>
</li>

      </ul>
   </nav>
</div>
</header>

<body>



<div class="wrapper">

   <div class="general">
   <?php

$trivia_for_today = new Trivia(); //generez trivia of the day daca este cazul
$_SESSION['trivia_id'] = $trivia_for_today->chosen_trivia;

?>

       <a href="trivia.php" class="slot1 trivia">

           <h2 style="top:15%;left:23%;position:absolute;">Trivia of the day</h2>
           <?php
$stmt = $db->conn->prepare("SELECT question FROM trivia WHERE id=?");
$stmt->bind_param('i', $_SESSION['trivia_id']);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
               <p style="top:220px;left:20px;right:20px;position:absolute;font-size:20px;">Question: ' . $row['question'] . ' </p>
               ';
} //afisez intrebarea pe prima pagina
?>

           <button type="button" name="play-trivia" class="button2"><span>Play</span></button>


        </a>

   <div class="slot2 leaderboard">

   <?php
$query = "SELECT u.id,u.username,u.profile_path,SUM(ts.score) AS score FROM trivia_score ts JOIN users u ON u.id=ts.id_user GROUP BY u.username ORDER BY 4 DESC LIMIT 3";
$res = $db->conn->query($query);
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
 <a href="profilepage.php?id=' . $row['id'] . '" class="top-player">
           <img src="' . $row['profile_path'] . '">
           <p> ' . $row['username'] . '</p>
           <h5> ' . $row['score'] . ' points </h5>
      </a>
';
} //top 3 jucatori cu cele mai multe puncte obtinute la trivia
?>


   </div>

   <div class="slot3 games">

   <?php
$query = "SELECT g.title,g.banner_path,lib.id_game,SUM(lib.hours) FROM libraries lib JOIN games g ON g.id=lib.id_game GROUP BY id_game ORDER BY 4 DESC LIMIT 5";
$res = $db->conn->query($query);
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {

    echo '
<div class="slide-games fade">
        <a href="gamepage.php?gameid=' . $row['id_game'] . '">
        <img src="' . $row['banner_path'] . '" style="width:100%">
        <div class="text">' . $row['title'] . '</div>
        </a>
     </div>
   ';

} //cele mai jucate jocuri
?>


     <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
  <a class="next" onclick="plusSlides(1)">&#10095;</a>

   </div>

   </div>

</div>


</body>

<script>
var slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}


function showSlides(n) {
  var i;
  var slides = document.getElementsByClassName("slide-games");

  if (n > slides.length) {slideIndex = 1}
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
  }
  slides[slideIndex-1].style.display = "block";
}
</script>



    </html>
