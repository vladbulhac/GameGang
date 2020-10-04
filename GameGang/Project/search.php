<?php
require_once './utils/Database.php';

session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');
?><!DOCTYPE html>
<html>
<head>
<title>Search Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/search-style.css" rel="stylesheet" type="text/css">
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
if ($page == "" || $page == "1" || !isset($_GET['page']) || empty($_GET['page'])) {
    $page_var = 0;} else {
    $page_var = ($page * 3) - 3;
}
?>

<div class="wrapper">
<h2 class="hr-sect">Search results for <?php echo $_GET['search']; ?></h2>

<div class="search-results">
    <?php
$_SESSION['found_results'] = 0;
$search_val = $_GET['search']; //ce cauta utilizatorul

$like_var = "%" . $search_val . "%";
$query = "SELECT id,username,profile_path,acc_type FROM users WHERE username LIKE  ? OR acc_type LIKE  ? OR email LIKE ? ORDER BY username ASC LIMIT ?,3 ";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('sssi', $like_var, $like_var, $like_var, $page_var);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_array(MYSQLI_ASSOC)) { //rezultate utilizatori
    $_SESSION['found_results'] = 1;
    $query_friends = "SELECT id FROM friends WHERE id_user1=?";
    $stmt_friends = $db->conn->prepare($query_friends);
    $stmt_friends->bind_param('i', $row['id']);
    $stmt_friends->execute();
    $res_friends = $stmt_friends->get_result();
    $row_num = $res_friends->num_rows;

    echo '
<a href="profilepage.php?id=' . $row['id'] . '" class="result">
<img src="' . $row['profile_path'] . '" class="result-img" style="width:150px;height:150px;">
<h2 class="result-title">' . $row['username'] . '</h2>
<p class="result-from">USER</p>';
    $query_game = "SELECT hours FROM libraries WHERE id_user=? ORDER BY hours DESC LIMIT 1";
    $stmt_game = $db->conn->prepare($query_game);
    $stmt_game->bind_param('i', $row['id']);
    $stmt_game->execute();
    $res_game = $stmt_game->get_result();
    $hours_played = 0;
    if ($row_game = $res_game->fetch_array(MYSQLI_ASSOC)) {
        $hours_played = $row_game['hours'];
    }
    $query_trivia = "SELECT SUM(score) AS sum FROM trivia_score WHERE id_user=?";
    $stmt_trivia = $db->conn->prepare($query_trivia);
    $stmt_trivia->bind_param('i', $row['id']);
    $stmt_trivia->execute();
    $res_trivia = $stmt_trivia->get_result();
    $trivia_score = 0;
    if ($row_trivia = $res_trivia->fetch_array(MYSQLI_ASSOC)) {
        $trivia_score = $row_trivia['sum'];
    }
    if ($trivia_score >= $hours_played) {
        echo '<p class="result-mostplayed">Trivia score: ' . $trivia_score . ' points</p>';
    } else {
        echo '<p class="result-mostplayed">Most played game: ' . $hours_played . ' hours</p>';
    }

    if ($row_num > 0) {
        echo '<p class="result-alsofriend">Friend with ' . $row_num . '  users</p>';
    }
    echo ' <button type="submit" name="profilebtn" class="button"><span>PROFILE</span></button>
</a>
<hr>';
}
?>

<?php

if (isset($_SESSION['userid'])) { //rezultate din libraria utilizatorului daca acesta este logat
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? AND (g.title LIKE ? OR g.genre LIKE ?) LIMIT ?,3";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('issi', $_SESSION['userid'], $like_var, $like_var, $page_var);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $_SESSION['found_results'] = 1;
        echo '<a href="gamepage.php?gameid=' . $row['id'] . '" class="result">
<img src="' . $row['thumbnail_path'] . '" class="result-img" style="width:150px;height:150px;">
<h2 class="result-title">' . $row['title'] . '</h2>
<p class="result-from">Library</p>
<p class="result-genre">Genre:' . $row['genre'] . '</p>
<button type="submit" name="playbtn" class="button"><span>PLAY</span></button>
</a>
<hr>';
    }
}
?>

<?php

$query = "SELECT id,title,genre,thumbnail_path FROM games WHERE (title LIKE ? OR genre LIKE ?) AND approved=1 LIMIT ?,3";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('ssi', $like_var, $like_var, $page_var);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_array(MYSQLI_ASSOC)) { //rezultate din lista de jocuri
    $_SESSION['found_results'] = 1;
    echo '<a href="gamepage.php?gameid=' . $row['id'] . '" class="result">
<img src="' . $row['thumbnail_path'] . '" class="result-img" style="width:150px;height:150px;">
<h2 class="result-title">' . $row['title'] . '</h2>
<p class="result-from">Games</p>
<p class="result-genre">Genre:' . $row['genre'] . '</p>
<button type="submit" name="addbtn" class="button"><span>ADD</span></button>
</a>
<hr>';
}
?>

<?php

if (isset($_SESSION['userid'])) { //rezultate din lista de prieteni a utilizatorului daca acesta este logat
    $query = "SELECT u.id,u.username,u.acc_type,u.profile_path FROM users u JOIN friends f ON u.id=f.id_user2 WHERE f.id_user1=? AND(u.username LIKE  ? OR u.acc_type LIKE ? OR u.email LIKE ?) LIMIT ?,3";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('isssi', $_SESSION['userid'], $like_var, $like_var, $like_var, $page_var);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $_SESSION['found_results'] = 1;

        echo '
<a href="profilepage.php?id=' . $row['id'] . '" class="result">
<img src="' . $row['profile_path'] . '" class="result-img" style="width:150px;height:150px;">
<h2 class="result-title">' . $row['username'] . '</h2>
<p class="result-from">Friends</p>';

        $query_game = "SELECT hours FROM libraries WHERE id_user=? ORDER BY hours DESC LIMIT 1";
        $stmt_game = $db->conn->prepare($query_game);
        $stmt_game->bind_param('i', $row['id']);
        $stmt_game->execute();
        $res_game = $stmt_game->get_result();
        $hours_played = 0;
        if ($row_game = $res_game->fetch_array(MYSQLI_ASSOC)) {
            $hours_played = $row_game['hours'];
        }
        $query_trivia = "SELECT SUM(score) AS sum FROM trivia_score WHERE id_user=?";
        $stmt_trivia = $db->conn->prepare($query_trivia);
        $stmt_trivia->bind_param('i', $row['id']);
        $stmt_trivia->execute();
        $res_trivia = $stmt_trivia->get_result();
        $trivia_score = 0;
        if ($row_trivia = $res_trivia->fetch_array(MYSQLI_ASSOC)) {
            $trivia_score = $row_trivia['sum'];
        }
        if ($trivia_score >= $hours_played) {
            echo '<p class="result-mostplayed">Trivia score: ' . $trivia_score . ' points</p>';
        } else {
            echo '<p class="result-mostplayed">Most played game: ' . $hours_played . ' hours</p>';
        }
        $query_common = "SELECT g.title FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? AND g.id IN(SELECT id FROM libraries WHERE id_user=? ORDER BY hours DESC) LIMIT 1";

        $stmt_common = $db->conn->prepare($query_common);
        $stmt_common->bind_param('ii', $_SESSION['userid'], $row['id']);
        $stmt_common->execute();
        $res_common = $stmt_common->get_result();
        $games_common = null;
        if ($row_common = $res_common->fetch_array(MYSQLI_ASSOC)) {
            $games_common = $row_common['title'];
        }
        if ($games_common != null) {
            echo '<p class="result-commongames">Game in common: ' . $games_common . '</p>';
        }
        echo '<button type="submit" name="profilebtn" class="button"><span>PROFILE</span></button>
</a>
<hr>';
    }
}
?>

<?php
if ($_SESSION['found_results'] == 0) { //nu au fost gasit rezultate pentru ce cauta utilizatorul
    echo '<p>No results found for ' . $_GET['search'] . '</p>';
}
?>



</div>

<div style="position:absolute;position:3500px;left:1200px;">
<ul class="pag_ul">
    <?php
$row_num = 0;
$query = "SELECT id,username,profile_path,acc_type FROM users WHERE (username LIKE  ? OR acc_type LIKE  ? OR email LIKE ?) AND session_type='public' ORDER BY username ASC ";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('sss', $like_var, $like_var, $search_val);
$stmt->execute();
$res = $stmt->get_result();

$row_num = $row_num + $res->num_rows;

if (isset($_SESSION['userid'])) {
    $query = "SELECT g.id,g.title,g.genre,g.thumbnail_path FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? AND (g.title LIKE ? OR g.genre LIKE ?)";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('iss', $_SESSION['userid'], $like_var, $like_var);
    $stmt->execute();
    $res = $stmt->get_result();

    $row_num = $row_num + $res->num_rows;
}

$query = "SELECT id,title,genre,thumbnail_path FROM games WHERE (title LIKE ? OR genre LIKE ?) AND approved=1";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('ss', $like_var, $like_var);
$stmt->execute();
$res = $stmt->get_result();

$row_num = $row_num + $res->num_rows;

if (isset($_SESSION['userid'])) {
    $query = "SELECT u.id,u.username,u.acc_type,u.profile_path FROM users u JOIN friends f ON u.id=f.id_user2 WHERE f.id_user1=? AND(u.username LIKE ? OR u.acc_type LIKE  ? OR u.email LIKE ?)";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('isss', $_SESSION['userid'], $search_val, $like_var, $like_var);
    $stmt->execute();
    $res = $stmt->get_result();

    $row_num = $row_num + $res->num_rows;
}
$results_page = ceil($row_num / 12); //paginarea rezultatelor, afisez 3 jocuri din games, 3 jocuri din librarie, 3 utilizatori din users si 3 utilizatori din lista de prieteni pe pagina
if ($results_page > 1) {
    for ($i = max(1, $page - 2); $i <= min($page + 2, $results_page); $i++) {
        echo "<li><a href='search.php?page=$i&search=$search_val'>$i </a></li>";
    }
}
?>
</ul>
</div>

</div>

</body>
</html>
