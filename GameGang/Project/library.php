<?php
require_once './utils/Database.php';

session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');

$filter_genre = $_GET['genre'];
$filter_hours = $_GET['hours'];
?><!DOCTYPE html>
<html>
<head>
<title>Library Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/library-style.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=ABeeZee' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css?family=Baloo+Bhai&display=swap" rel="stylesheet">
</head>

<header>
<div class="top-menu">
   <nav>
       <ul class="nav_links">
           <li><a href="index.php" style="font-size:50px;position:absolute;top:10px;left:5px;">&#8249;</li>
          <li><a href="index.php">Home</a></li>
          <li><a href="leaderboard.php">Leaderboard</a></li>
          <li><a href="games.php?page=1&genre=none">Games</a></li>
          <li><a href="library.php?page=1&hours=none&genre=none">My Library</a></li>
          <?php
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
$page = $_GET['page'];
if ($page == "" || $page == "1") {
    $page_var = 0;} else {
    $page_var = ($page * 12) - 12;
}
?>

<div class="wrapper">
   <div class="game-list">
      <h1 class="hr-sect">My Library</h1>
       <div>
<?php
$ok = 0;
if ($filter_genre != 'none' && $filter_hours != 'none') { //au fost aplicate filtrele pentru numarul de ore si genul jocului
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? AND g.genre=? AND lib.hours<? ORDER BY lib.hours DESC LIMIT ?,12";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('isii', $_SESSION['userid'], $filter_genre, $filter_hours, $page_var);
} else
if ($filter_genre != 'none') { //a fost aplicat numai filtrul pentru genuri
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? AND g.genre=? ORDER BY lib.hours DESC LIMIT ?,12";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('isi', $_SESSION['userid'], $filter_genre, $page_var);
} else
if ($filter_hours != 'none') { //a fost aplicat numai filtrul pentru numarul de ore
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? AND lib.hours<? ORDER BY lib.hours DESC LIMIT ?,12";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('iii', $_SESSION['userid'], $filter_hours, $page_var);
} else { //nu a fost aplicat niciun filtru
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? ORDER BY lib.hours DESC LIMIT ?,12";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('ii', $_SESSION['userid'], $page_var);
}

$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    $ok = 1;
    echo '
<a href="gamepage.php?gameid=' . $row['id'] . '">
<img src="' . $row['thumbnail_path'] . '" alt="game_image">
<div>
<h2>' . $row['title'] . '</h2>
<button type="submit" name="playbtn" class="button" ><span>PLAY</span></button>
</div>
<p class="genre">' . $row['genre'] . '</p>
</a>
';
}

?>

    </div>
    <?php
if ($ok == 0) { //nu s-a gasit niciun joc in librarie, afisez mesaj
    echo "<a href='games.php?page=1&genre=none' style='color:#24252A;position:absolute;top:200px;font-size:25px;'>There are no games in your library,access the Games page to get some!</a>";
}
?>
</div>

<div style="position:absolute;top:1100px;left:1200px;">
<ul class="pag_ul">
    <?php
if ($filter_genre != 'none' && $filter_hours != 'none') { //toate rezultatele aplicand amandoua filtrele
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? AND g.genre=? AND lib.hours<?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('isi', $_SESSION['userid'], $filter_genre, $filter_hours);
} else
if ($filter_genre != 'none') { //toate rezultatele aplicat numai filtrul pentru genuri
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? AND g.genre=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('is', $_SESSION['userid'], $filter_genre);
} else
if ($filter_hours != 'none') { //toate rezultatele aplicand numai filtrul pentru orele jucate
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? AND lib.hours<?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('ii', $_SESSION['userid'], $filter_hours);
} else { //toate rezultatele aplicand niciun filtru
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['userid']);
}

$stmt->execute();
$res = $stmt->get_result();
$row_num = $res->num_rows;

$results_page = ceil($row_num / 12); //numarul de pagini necesar pentru a afisa 12 rezultate pe pagina
if ($results_page > 1) {
    for ($i = max(1, $page - 2); $i <= min($page + 2, $results_page); $i++) {
        echo "<li><a href='library.php?page=$i&hours=$filter_hours&genre=$filter_genre'>$i </a></li>";}
}
?>
</ul>
</div>

<aside>
    <h2 class="hr-sect"> Filters </h2>
  <div style="text-shadow: 1px 1px #24252A;">
    <p style="float:right;">Number of hours played</p><br>
    <form action="" style="margin-top:5px;" method="GET">
    <input type="hidden" name="page" value="1">
        <input type="hidden" name="genre" value="<?php echo $filter_genre; ?>">
        <select style="margin-top:5px;" name="hours" onchange="this.form.submit();">
        <option value="none">none</option>
        <option value="2">less than 2</option>
        <option value="5">less than 5</option>
        <option value="10">less than 10</option>
        <option value="20">less than 20</option>
        <option value="50">less than 50</option>
        <option value="100">less than 100</option>
</select>
  </form>
  </div>
  <hr>
  <div style="text-shadow: 1px 1px #24252A;">
    <p style="float:right;">Genre</p><br>
    <form action="" style="margin-top:5px;" method="GET">
    <input type="hidden" name="page" value="1">
        <input type="hidden" name="hours" value="<?php echo $filter_hours; ?>">
        <select style="margin-top:5px;" name="genre" onchange="this.form.submit();">
        <option value="none">none</option>
    <?php
$query = "SELECT g.genre FROM games g JOIN libraries lib ON g.id=lib.id_game WHERE lib.id_user=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    echo '
<option value=' . $row['genre'] . '>' . $row['genre'] . '</option>';
} //afisez ca optiuni de setare a filtrului pentru genuri, numai genurile jocurilor din libraria utilizatorului
?>
</select>
  </form>
  </div>
  <a href="library.php?page=1&hours=none&genre=none" style="float:right;"><button type="button" name="rest_filters">Reset Filters</button></a>


 </aside>
</div>



</body>

<footer>
<h1 class="hr-sect">Your friends play</h1>

<div class="suggestions">

<?php
$query = "SELECT g.title,g.thumbnail_path,u.username FROM games g JOIN libraries lib ON lib.id_game=g.id JOIN users u ON lib.id_user=u.id JOIN friends f ON f.id_user2=u.id WHERE f.id_user1=? ORDER BY lib.hours DESC LIMIT 9";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();

$counter = 1;
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    if ($counter == 1) {
        echo
            '<div class="friendsgame fade flexboxfriends"> ';
    }
    echo '
<div >
 <img src="' . $row['thumbnail_path'] . '" alt="game_image">
 <div class="whofriend">' . $row['username'] . '</div>
 <div class="whatgame">' . $row['title'] . '</div>
   </div>
';

    if ($counter == 3) { //inchid divul de la flexboxfriends
        $counter = 0;
        echo '</div>';
    }
    $counter++;
}
if ($counter < 3) { //inchid divul de la flexboxfriends in cazul in care am mai putin de trei rezultate
    echo '</div>';
}
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
