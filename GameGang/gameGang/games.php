<?php
require_once './Controllers/Database.php';

session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');
?><!DOCTYPE html>
<html>

<head>
<title>Games Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/store-style.css" rel="stylesheet" type="text/css">
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
         <li> <form action="search.php">
             <input type="hidden" name="page" value="1">
             <input type="text" name="search" placeholder="I am looking for..." style=width:300px>
             <button type="submit">Search</button>
         </form>
</li>
      </ul>
   </nav>
</div>
</header>

<body>
<?php
$page = $_GET['page'];
if ($page == "" || $page == "1") {
    $page_var = 0;} else {
    $page_var = ($page * 12) - 12;
}
?>
<div class="wrapper">
<div class="game-list">
    <h1 class="hr-sect">List of Games</h1>
    <div>
<?php

$filter_genre = $_GET['genre'];

$ok = 0;
//daca nu a fost aplicat filtrul pentru genuri atunci afisez toate jocurile care au fost aprobate
if ($filter_genre == 'none') {$query = "SELECT id,title,genre,thumbnail_path FROM games WHERE approved=1 ORDER BY id LIMIT ?,12";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $page_var);
} else { //a fost aplicat filtrul pentru genuri asa ca afisez toate jocurile care au acel gen si au fost aprobate
    $query = "SELECT id,title,genre,thumbnail_path FROM games WHERE approved=1 AND genre=? ORDER BY id LIMIT ?,12";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('si', $filter_genre, $page_var);
}
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    $ok = 1;
    echo ' <a href="gamepage.php?gameid=' . $row['id'] . '">
<img src="' . $row['thumbnail_path'] . '" alt="game_image">
<div>
<h2>' . $row['title'] . '</h2>
</div>
<p class="genre">' . $row['genre'] . '</p>
</a> ';
}

?>

<?php
if ($ok == 0) { //nu s-a gasit niciun joc,afisez mesaj
    echo '<p style="font-size:20px;color:#24252A;">No results found for GENRE=' . $filter_genre . '</p>';
}
?>


    </div>


    <div style="position:absolute;top:1100px;left:1200px;">
<ul class="pag_ul">
    <?php
//paginarea pentru cand nu s-a aplicat filtrul
if ($filter_genre == 'none') {$query = "SELECT id,title,genre,thumbnail_path FROM games WHERE approved=1 ORDER BY id";
    $stmt = $db->conn->prepare($query);
} else { //paginarea pentru cand s-a aplicat filtrul
    $query = "SELECT id,title,genre,thumbnail_path FROM games WHERE approved=1 AND genre=? ORDER BY id";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('s', $filter_genre);
}
$stmt->execute();
$res = $stmt->get_result();

$row_num = $res->num_rows;

$results_page = ceil($row_num / 12); //impart numarul total de rezultate la cate vreau sa afisez pe o pagina
if ($results_page > 1) {
    for ($i = max(1, $page - 2); $i <= min($page + 2, $results_page); $i++) {
        echo "<li><a href='games.php?page=$i&genre=$filter_genre'>$i </a></li>";}
}

?>
</ul>
</div>
</div>

<aside>
    <h2 class="hr-sect"> Filters </h2>
<div style="text-shadow: 1px 1px #24252A;">
    <p style="float:right;">Genre</p><br>
    <form action="" method="GET">
    <input type="hidden" name="page" value="1">
    <select style="margin-top:5px;" name="genre" onchange="this.form.submit();">
        <option value="none">none</option>
        <?php
$query = "SELECT DISTINCT genre FROM games ORDER BY 1 ASC";
$res = $db->conn->query($query);

if ($res->num_rows > 0) { //dau ca optiuni de filtru pe genuri numai genurile existente in baza de date
    while ($row = $res->fetch_assoc()) {
        echo ' <option value="' . $row['genre'] . '">' . $row['genre'] . '</option>';
    }
}
?>
</select>
</form>
</div>
<hr>

<a href="games.php?page=1&genre=none" style="float:right;"><button type="button" name="rest_filters">Reset Filters</button></a>

<div class="rec-games">
    <p style="text-shadow: 1px 1px #24252A;">Others play:</p>
<br>
<?php
$query = "SELECT g.title,g.thumbnail_path,u.username FROM games g JOIN libraries lib ON lib.id_game=g.id JOIN users u ON u.id=lib.id_user ORDER BY lib.hours DESC LIMIT 9 ";
$res = $db->conn->query($query);

while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
      <div class="recommendations fade">
<img src="' . $row['thumbnail_path'] . '" alt="game_image">
<div class="whofriend">' . $row['username'] . '</div>
<div class="whatgame">' . $row['title'] . '</div>
   </div>
    ';
} //recomandari de jocuri pe baza celor mai jucate

?>
<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
<a class="next" onclick="plusSlides(1)">&#10095;</a>


</div>

</aside>
</div>

</body>

<script>
var slideIndex=1;
showSlides(slideIndex);

function plusSlides(n){
    showSlides(slideIndex+=n);
}

function showSlides(n){
    var i;
    var slides = document.getElementsByClassName("recommendations");
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
