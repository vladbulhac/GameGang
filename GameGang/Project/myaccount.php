<?php

require_once './utils/Database.php';
session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');

if ($_SESSION['acc_type'] == 'user') {
    $user_app_type = 0;//daca contul este de tip user acesta trebuie sa primeasca aprobare la jocurile/trivia adaugate
} else {
    $user_app_type = 1;//daca contul este de tip admin acesta poate adaugat jocuri/trivia fara aprobare
}

?><!DOCTYPE html>
<html>
<head>
<title>My Account Page</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/myaccount-style.css" rel="stylesheet" type="text/css">
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

<?php
$row_num = 0;//pentru a afisa numarul de notificari
$query = "SELECT id FROM games WHERE approved=0";
$res = $db->conn->query($query);
$row_num = $row_num + $res->num_rows;

$query = "SELECT id FROM trivia WHERE approved=0";
$res = $db->conn->query($query);
$row_num = $row_num + $res->num_rows;

$query = "SELECT id FROM trivia_answers WHERE approved=0";
$res = $db->conn->query($query);
$row_num = $row_num + $res->num_rows;
?>

<div class="wrapper">
     <?php if ($row_num > 0 && $_SESSION['acc_type']=='admin') {echo '<span class="badge">' . $row_num . '</span>';}?>
<div class="tab">
<button class="tablinks" onclick="changeSetting(event,'profile')" id="defaultShow">My profile</button>
<button class="tablinks" onclick="changeSetting(event,'friends')">Friend list</button>
<button class="tablinks" onclick="changeSetting(event,'notifications')" >Notifications</button>
<button class="tablinks" onclick="changeSetting(event,'admin')">Admin</button>
</div>

<div class="acc-info tabcontent" id="profile">
<?php
$query = "SELECT username,email,acc_type,profile_path,register_date FROM users WHERE id=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {//informatii despre contul meu cum ar fi imaginea de profil,numele,data inregistrarii,emailul,etc
    echo '
<img src="' . $row['profile_path'] . '" class="acc-profile-img">
<h2 class="acc-name">' . $row['username'] . '</h2>
<p class="acc-type">' . $row['acc_type'] . '</p>
<p class="acc-registr-date">' . $row['register_date'] . '</p>
<p class="acc-email">' . $row['email'] . '</p>
<button type="button" name="editbtn" class="button2"><span><a href="#edit">Edit Profile</a></span></button>
';
}
echo '<h2 class="hr-sect">My stats</h2>';
$query = "SELECT g.title,g.thumbnail_path,lib.hours FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? ORDER  BY  lib.hours DESC LIMIT 1";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {//cel mai jucat joc
    echo '

<img src="' . $row['thumbnail_path'] . '" class="mostplayed-game">
<p class="mostplayed-title">' . $row['title'] . '</p>
<p class="mostplayed-hours"><b>' . $row['hours'] . '</b> hours</p>
';
}

$query = "SELECT g.title,g.thumbnail_path,lib.hours FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? ORDER  BY  lib.hours ASC LIMIT 1";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {//cel mai putin jucat joc
    echo '
<img src="' . $row['thumbnail_path'] . '" class="leastplayed-game">
<p class="leastplayed-title">' . $row['title'] . '</p>
<p class="leastplayed-hours"><b>' . $row['hours'] . '</b> hours</p>
';
}

$query = "SELECT SUM(lib.hours) AS sum FROM libraries lib WHERE lib.id_user=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {//numarul total de ore petrecut in jocuri
    echo '
<p class="total-nr-hrs">Time played accross all games: <b>' . $row['sum'] . '</b> hours</p>
';
}

$query = "SELECT id_user2 FROM friends WHERE id_user1=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
echo '
<p class="total-nr-friends">You have <b>' . $res->num_rows . '</b> friends</p>
';

$query = "SELECT SUM(score) AS score FROM trivia_score WHERE id_user=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {//numarul de puncte obtinute la trivia
    if($row['score']==null){$row['score']=0;}
    echo '
<p class="total-nr-points">You have <b>' . $row['score'] . '</b> trivia points</p>
';
}

$query = "SELECT g.genre,COUNT(lib.id_game) AS count FROM games g JOIN libraries lib ON lib.id_game=g.id WHERE lib.id_user=? GROUP BY g.genre ORDER BY 2 DESC";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
echo '<p class="games-by-genre">Games by genre</p>
<ul class="list-of-genres">';
$stmt->store_result();
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {//numarul de jocuri pe genuri
    echo '<li><p>' . $row['genre'] . ':<b>' . $row['count'] . '</b> games</p></li>';
}
echo '</ul>';


$query = "SELECT s.played_at,s.place,g.title FROM squad s JOIN games g ON g.id=s.id_game WHERE  s.id_user2=? ORDER BY played_at DESC LIMIT 1";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
$stmt->store_result();
if ($row = $res->fetch_array(MYSQLI_ASSOC)) {//cand am jucat ultima data cu altii, cu cine am jucat, pe ce loc am fost in clasament,ce joc am jucat
    echo '
<div class="squad-play">
      <p style="float:left;">Last squad play:</p>
      <p style="float:left;margin-left:10px;">' . $row['played_at'] . '</p>
      <p style="float:left;margin-left:10px;">Your place was ' . $row['place'] . ' in the leaderboard and you played ' . $row['title'] . ' with:
';
    $played = '' . $row['played_at'] . '';
    $query = "SELECT u.username FROM squad s JOIN users u ON u.id=s.id_user1 WHERE s.id_user1!=? AND s.id_user2=? AND played_at=? UNION ALL SELECT u.username FROM squad s JOIN users u ON u.id=s.id_user2 WHERE s.id_user2!=? AND s.id_user1=? AND played_at=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('iisiis', $_SESSION['userid'], $_SESSION['userid'], $played, $_SESSION['userid'], $_SESSION['userid'], $played);
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

?>
</div>
<?php
$notif_page = $_GET['notif_page'];
$page_friends = $_GET['friend_page'];
if ($page_friends == "" || $page_friends == "1") {
    $page_var = 0;} else {
    $page_var = ($page_friends * 12) - 12;
}
if ($notif_page == "" || $notif_page == "1") {
    $page_var1 = 0;} else {
    $page_var1 = ($notif_page * 2) - 2;
}
?>
<div class="friend-list tabcontent" id="friends">
      <div class="friends">
<?php 

$query = "SELECT u.id,u.profile_path,u.username FROM users u JOIN friends f ON u.id=f.id_user2 WHERE f.id_user1=? LIMIT ?,12";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('ii', $_SESSION['userid'], $page_var);
$stmt->execute();
$res = $stmt->get_result();
$ok = 0;
$stmt->store_result();
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {//lista de prieteni
    $ok = 1;
    echo '
<a href="profilepage.php?id=' . $row['id'] . '">
<img src="' . $row['profile_path'] . '">
<p>' . $row['username'] . '</p>
</a>
';
}

?>

<?php if ($ok == 0) {
    echo 'Your friend list is empty';
}?>

     </div>
     <div style="position:absolute;top:1100px;left:1200px;">
<ul class="pag_ul">
<?php 
$query = "SELECT id FROM friends WHERE id_user1=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['userid']);
$stmt->execute();
$res = $stmt->get_result();
$results_page = ceil($res->num_rows / 12);//paginarea listei de prieteni
if ($results_page > 1) {
    for ($i = 1; $i <= $results_page; $i++) {
        echo "<li><a href='myaccount.php?friend_page=$i&notif_page=$notif_page'>$i </a></li>";}}
?>
</ul>
</div>
</div>

<div class="history-list tabcontent" id="notifications">
         <div class="notification-list">
         <?php 
if($_SESSION['acc_type']!='admin'){//afisez un mesaj daca userul nu este admin
   echo '<h2 style="color:white;">You must be an admin to access this page!</h2>';
}
else {//daca este admin atunci are acces la o lista cu toate jocurile,intrebarile,raspunsurile care au fost adaugate de useri si care nu au fost inca aprobate

$ok = 0;
$query = "SELECT id,title,genre,thumbnail_path FROM games WHERE approved=0 LIMIT ?,2";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $page_var1);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    $ok = 1;
    echo '
<div class="notification-info">
<img src="' . $row['thumbnail_path'] . '">
<h2>' . $row['title'] . '</h2>
<p>Genre: <b>' . $row['genre'] . '</b></p>
<a href="approve.php?gid=' . $row['id'] . '"><button type="submit" name="approve_btn" style="top:-30px;left:400px;width:50px;height:30px;position:absolute;">A</button></a>
<a href="dapprove.php?gid=' . $row['id'] . '"><button type="submit" name="dapprove_btn" style="top:-30px;left:450px;width:50px;height:30px;position:absolute;">X</button></a>
</div>
';
}

$query = "SELECT id,question FROM trivia WHERE approved=0 LIMIT ?,2";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $page_var1);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    $ok = 1;
    echo '
<div class="notification-info">
    <h2 style="left:10px;">' . $row['question'] . '</h2>
    <p><b>Question</b></p>
      <a href="approve.php?qid=' . $row['id'] . '"><button type="submit" name="approve_btn" style="top:-30px;left:400px;width:50px;height:30px;position:absolute;">A</button></a>
      <a href="dapprove.php?qid=' . $row['id'] . '"><button type="submit" name="dapprove_btn" style="top:-30px;left:450px;width:50px;height:30px;position:absolute;">X</button></a>
</div>
';
}

$query = "SELECT t.question,ta.id,ta.answer,ta.type FROM trivia_answers ta JOIN trivia t ON t.id=ta.id_trivia WHERE ta.approved=0 LIMIT ?,2";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $page_var1);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    $ok = 1;
    echo '
<div class="notification-info">
    <h2 style="left:10px;">' . $row['question'] . '</h2>
    <p style="left:10px;">Answ: ' . $row['answer'] . '<b> ' . $row['type'] . '</b></p>
      <a href="approve.php?aid=' . $row['id'] . '"><button type="submit" name="approve_btn" style="top:-30px;left:400px;width:50px;height:30px;position:absolute;">A</button></a>
      <a href="dapprove.php?aid=' . $row['id'] . '"><button type="submit" name="dapprove_btn" style="top:-30px;left:450px;width:50px;height:30px;position:absolute;">X</button></a>
</div>
';
}

if ($ok == 0) {//afisez un mesaj pentru cazul in care pagina de notificari este goala
    echo '<h1 style="color:white;">Notification list is empty!</h1>';
}
}
?>



         </div>
         <div style="position:absolute;top:900px;left:900px;">
<ul class="pag_ul">
<?php 
$row_num = 0;
$query = "SELECT id FROM games WHERE approved=0";
$res = $db->conn->query($query);
$row_num = $row_num + $res->num_rows;

$query = "SELECT id FROM trivia WHERE approved=0";
$res = $db->conn->query($query);
$row_num = $row_num + $res->num_rows;

$query = "SELECT id FROM trivia_answers WHERE approved=0";
$res = $db->conn->query($query);
$row_num = $row_num + $res->num_rows;

$results_page = ceil($row_num / 5);//paginarea pentru notificari, afisez cate 2 jocuri,2 intrebari si 2 raspunsuri pe pagina
if ($results_page > 1) {
    for ($i = max(1, $notif_page - 2); $i <= min($notif_page + 2, $results_page); $i++) {
        echo "<li><a href='myaccount.php?friend_page=$page_friends&notif_page=$i' style='color:white;'>$i </a></li>";}}

?>
</ul>
</div>
</div>

<div class="admin-control tabcontent" id="admin">
<h1 class="hr-sect" style="top:5px;">Add  game</h1>

<form action="" method="POST">
      <label for="game-name" style="margin:5px;"><b>Title of the game:</b></label><br>
      <input type="text" placeholder="One title can be entered..." name="new_game_name" style="margin:5px;"><br>
      <label for="game-genre" style="margin:5px;"><b>Genre:</b></label><br>
      <input type="text" placeholder="One genre can be entered..." name="new_game_genre" style="margin:5px;"><br>
      <label for="game-banner" style="margin:5px;"><b>Banner path:</b></label><br>
      <input type="text" placeholder="Must be like: .\images\FolderName\banner.extension" name="new_game_banner" style="margin:5px;"><br>
      <label for="game-thumb" style="margin:5px;"><b>Thumbnail path:</b></label><br>
      <input type="text" placeholder="Must be like: .\images/FolderName\thumbnail.extension" name="new_game_thumb" style="margin:5px;"><br>

      <button type="submit" name="add_game_btn" class="button1" style="top:255px;left:600px;"><span>Add</span></button>

    </form>
    <?php 

if (isset($_POST['add_game_btn'])) {//adaug un joc 
    if (!empty($_POST['new_game_name']) && !empty($_POST['new_game_genre']) && !empty($_POST['new_game_banner']) && !empty($_POST['new_game_thumb'])) {
        $query = "INSERT INTO games (title,genre,banner_path,thumbnail_path,approved) VALUES (?,?,?,?,?)";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('ssssi', $_POST['new_game_name'], $_POST['new_game_genre'], $_POST['new_game_banner'], $_POST['new_game_thumb'], $user_app_type);
        $stmt->execute();

    } else {
        echo '<p style="position:relative;color:red;left:-300px;">Complete correctly all inputs!</p>';
    }

}
?>
    <h1 class="hr-sect" style="top:400px;">Add Trivia</h1>
    <form action="" method="POST" style="top:500px;">
    <label for="add_question" style="margin:5px;"><b>Question:</b></label><br>
        <input type="text" placeholder="Enter question...?" name="add_question" style="margin:5px;"><br>
        <label for="add_points" style="margin:5px;"><b>Points:</b></label><br>
        <input type="text" placeholder="Number of points" name="add_points" style="margin:5px;"><br>
        <label for="add_answc" style="margin:5px;"><b>Correct answer:</b></label><br>
        <input type="text" placeholder="Correct answer" name="add_answc" style="margin:5px;"><br>
        <label for="add_answ1" style="margin:5px;"><b>Wrong answer:</b></label><br>
        <input type="text" placeholder="Wrong answer" name="add_answ1" style="margin:5px;"><br>
        <label for="add_answ2" style="margin:5px;"><b>Wrong answer:</b></label><br>
        <input type="text" placeholder="Wrong answer" name="add_answ2" style="margin:5px;"><br>
        <label for="add_answ3" style="margin:5px;"><b>Wrong answer:</b></label><br>
        <input type="text" placeholder="Wrong asnwer" name="add_answ3" style="margin:5px;"><br>
        <button type="submit" name="add_trivia" class="button1" style="top:55px;left:600px;"><span>Add Trivia</span></button>

</form>
<?php 

if (isset($_POST['add_trivia'])) {//adaug trivia
    $added_trivia_id = 0;
    if (!empty($_POST['add_question']) && !empty($_POST['add_answc']) && !empty($_POST['add_answ1']) && !empty($_POST['add_answ2']) && !empty($_POST['add_answ3']) && !empty($_POST['add_points'])) {
        $query = "INSERT INTO trivia (question,points,approved) VALUES (?,?,?)";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('sii', $_POST['add_question'], $_POST['add_points'], $user_app_type);
        $stmt->execute();

        $query = "SELECT id FROM trivia ORDER BY 1 DESC LIMIT 1";
        $res = $db->conn->query($query);
        if ($row = $res->fetch_assoc()) {
            $added_trivia_id = $row['id'];
        }
        if ($added_trivia_id != 0) {
            $type = "correct";
            $query = "INSERT INTO trivia_answers (id_trivia,answer,type,approved) VALUES (?,?,?,?)";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('issi', $added_trivia_id, $_POST['add_answc'], $type, $user_app_type);
            $stmt->execute();
            $type = "wrong";
            $query = "INSERT INTO trivia_answers (id_trivia,answer,type,approved) VALUES (?,?,?,?)";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('issi', $added_trivia_id, $_POST['add_answ1'], $type, $user_app_type);
            $stmt->execute();

            $query = "INSERT INTO trivia_answers (id_trivia,answer,type,approved) VALUES (?,?,?,?)";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('issi', $added_trivia_id, $_POST['add_answ2'], $type, $user_app_type);
            $stmt->execute();

            $query = "INSERT INTO trivia_answers (id_trivia,answer,type,approved) VALUES (?,?,?,?)";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('issi', $added_trivia_id, $_POST['add_answ3'], $type, $user_app_type);
            $stmt->execute();
        }
    } else {
        echo '<p style="position:relative;color:red;left:-300px;">Complete correctly all inputs!</p>';
    }
}
?>
<?php  

if($_SESSION['acc_type']=='admin'){//optiuni disponibile numai adminilor

echo'
    <h1 class="hr-sect" style="top:900px;">Update user type</h1>
    <form action="" method="POST" style="top:1050px;">
    <label for="update_for_acc_name" style="margin:5px;"><b>Username or Email:</b></label><br>
        <input type="text" placeholder="Of the account you want to make this change to.." name="update_for_acc_name" style="margin:5px;"><br>
        <label for="update_acc_type" style="margin:5px;"><b>Account type:</b></label><br>
        <input type="text" placeholder="User or Admin" name="update_acc_type" style="margin:5px;"><br>
        <button type="submit" name="update_user_btn" class="button1" style="top:55px;left:600px;"><span>Update</span></button>

</form>';

if (isset($_POST['update_user_btn'])) {//update la un user
    if (!empty($_POST['update_for_acc_name']) && !empty($_POST['update_acc_type'])) {
        $query = "SELECT id FROM users WHERE username=? OR email=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('ss', $_POST['update_for_acc_name'], $_POST['update_for_acc_name']);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $query = "UPDATE users SET acc_type=? WHERE id=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('si', $_POST['update_acc_type'], $row['id']);
            $stmt->execute();
        } else {
            echo '<p style="position:relative;color:red;left:-340px;">No such user was found in our database!</p>';
        }

    } else {
        echo '<p style="position:relative;color:red;left:-340px;">Complete correctly all inputs!</p>';
    }
}


echo'
<h1 class="hr-sect" style="top:1120px;">Remove game</h1>
    <form action="" method="POST" style="top:1310px;">
    <label for="game_title" style="margin:5px;"><b>Title of the game</b></label><br>
        <input type="text" placeholder="" name="game_title" style="margin:5px;"><br>

        <button type="submit" name="removebtn" class="button1" style="top:20px;left:600px;"><span>Remove Game</span></button>

</form>';

if (isset($_POST['removebtn'])) {//stergere joc
    if (!empty($_POST['game_title'])) {
        $query = "SELECT id FROM games WHERE title=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('s', $_POST['game_title']);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $query = "DELETE FROM libraries WHERE id_game=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();

            $query = "DELETE FROM squad WHERE id_game=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();

            $query = "DELETE FROM games WHERE title=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('s', $_POST['game_title']);
            $stmt->execute();

        } else {
            echo '<p style="position:relative;color:red;left:-340px;">We could not find this game in our database!</p>';
        }

    } else {
        echo '<p style="position:relative;color:red;left:-340px;">Complete correctly all inputs!</p>';
    }
}

echo'
<h1 class="hr-sect" style="top:1250px;">Remove user</h1>
    <form action="" method="POST" style="top:1500px;">
    <label for="user_delete" style="margin:5px;"><b>Username</b></label><br>
        <input type="text" placeholder="" name="user_delete" style="margin:5px;"><br>

        <button type="submit" name="deleteuserbtn" class="button1" style="top:20px;left:600px;"><span>Delete User</span></button>

</form>';

if (isset($_POST['deleteuserbtn'])) {//stergere user
    if (!empty($_POST['user_delete'])) {
        $query = "SELECT id FROM users WHERE username=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('s', $_POST['user_delete']);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $query = "DELETE FROM libraries WHERE id_user=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();

            $query = "DELETE FROM squad WHERE id_user1=? OR id_user2=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('ii', $row['id'], $row['id']);
            $stmt->execute();

            $query = "DELETE FROM friends WHERE id_user1=? OR id_user2=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('ii', $row['id'], $row['id']);
            $stmt->execute();

            $query = "DELETE FROM trivia_score WHERE id_user=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();

            $query = "DELETE FROM users WHERE id=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
        } else {
            echo '<p style="position:relative;color:red;left:-300px;">No such user in our database!</p>';
        }
    } else {
        echo '<p style="position:relative;color:red;left:-300px;">Complete correctly all inputs!</p>';
    }
}

echo'
<h1 class="hr-sect" style="top:1400px;">Remove trivia</h1>
    <form action="" method="POST" style="top:1680px;">
    <label for="delete_trivia" style="margin:5px;"><b>Question</b></label><br>
        <input type="text" placeholder="The question and its answers will be removed..." name="delete_trivia" style="margin:5px;"><br>

        <button type="submit" name="deletetriviabtn" class="button1" style="top:20px;left:600px;"><span>Delete Trivia</span></button>

</form>';


if (isset($_POST['deletetriviabtn'])) {//stergere trivia
    if (!empty($_POST['delete_trivia'])) {
        $query = "SELECT id FROM trivia WHERE question=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('s', $_POST['delete_trivia']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $query = "SELECT id FROM generated_trivia WHERE id_trivia=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo '<p style="position:relative;color:red;left:-300px;">This trivia cannot be removed because it is on the trivia of the day!</p>';
            } else {
                $query = "DELETE FROM trivia_score WHERE id_trivia=?";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('i', $row['id']);
                $stmt->execute();

                $query = "DELETE FROM trivia_answers WHERE id_trivia=?";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('i', $row['id']);
                $stmt->execute();

                $query = "DELETE FROM trivia WHERE id=?";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('i', $row['id']);
                $stmt->execute();
            }
        } else {
            echo '<p style="position:relative;color:red;left:-300px;">We could not find this question in our database!</p>';
        }
    } else {
        echo '<p style="position:relative;color:red;left:-300px;">Complete correctly all inputs!</p>';
    }
}



echo'
<h1 class="hr-sect" style="top:1550px;">Remove answer</h1>
    <form action="" method="POST" style="top:1880px;">
    <label for="delete_answer" style="margin:5px;"><b># Answer</b></label><br>
        <input type="text" placeholder="Enter the number of answer (1-4)..." name="delete_answer" style="margin:5px;"><br>
        <label for="delete_answerq" style="margin:5px;"><b>Question:</b></label><br>
        <input type="text" placeholder="Enter the question that has the answer you want to remove" name="delete_answerq" style="margin:5px;"><br>

        <button type="submit" name="deleteanswerbtn" class="button1" style="top:20px;left:600px;"><span>Delete Answer</span></button>

</form>';


if (isset($_POST['deleteanswerbtn'])) {//stergere raspuns trivia
    if (!empty($_POST['delete_answer']) && !empty($_POST['delete_answerq'])) {
        $query = "SELECT id FROM trivia WHERE question=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('s', $_POST['delete_answerq']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $query = "SELECT id FROM generated_trivia WHERE id_trivia=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo '<p style="position:relative;color:red;left:-300px;">You cannot change this trivia because it is on the trivia of the day!</p>';
            } else {
                $query = "SELECT id FROM trivia_answers WHERE id_trivia=? ORDER BY 1 ASC LIMIT 1";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('i', $row['id']);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($answid = $res->fetch_array(MYSQLI_ASSOC)) {
                    $query = "SELECT id FROM trivia_answers WHERE id=?";
                    $stmt = $db->conn->prepare($query);
                    $calc = $answid['id'] + $_POST['delete_answer'] - 1;
                    $stmt->bind_param('i', $calc);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $row_answ = $result->fetch_array(MYSQLI_ASSOC);
                        $query = "DELETE FROM trivia_answers WHERE id=?";
                        $stmt = $db->conn->prepare($query);
                        $stmt->bind_param('i', $row_answ['id']);
                        $stmt->execute();
                    } else {
                        echo '<p style="position:relative;color:red;left:-300px;">That answer does not exist!</p>';
                    }

                }
            }
        }} else {
        echo '<p style="position:relative;color:red;left:-300px;">Complete correctly all inputs!</p>';
    }
}





echo'
<h1 class="hr-sect" style="top:1800px;">Change answer</h1>
    <form action="" method="POST" style="top:2180px;">
    <label for="update_answer" style="margin:5px;"><b># Answer</b></label><br>
        <input type="text" placeholder="Enter the number of answer (1-4)..." name="update_answer" style="margin:5px;"><br>
        <label for="update_answerq" style="margin:5px;"><b>Question:</b></label><br>
        <input type="text" placeholder="Enter the question that has the answer you want to change" name="update_answerq" style="margin:5px;"><br>
        <label for="new_update_answer" style="margin:5px;"><b>New answer:</b></label><br>
        <input type="text" placeholder="Enter new answer..." name="new_update_answer" style="margin:5px;"><br>
        <label for="new_update_answer_type" style="margin:5px;"><b>Update answer type:</b></label><br>
        <input type="text" placeholder="Choose between correct or wrong" name="new_update_answer_type" style="margin:5px;"><br>

        <button type="submit" name="changeanswerbtn" class="button1" style="top:20px;left:600px;"><span>Update Answer</span></button>

</form>';



if (isset($_POST['changeanswerbtn'])) {//schimbare raspuns trivia
    if (!empty($_POST['update_answer']) && !empty($_POST['update_answerq'])  && !empty($_POST['new_update_answer'])) {
        $query = "SELECT id FROM trivia WHERE question=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('s', $_POST['update_answerq']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $query = "SELECT id FROM generated_trivia WHERE id_trivia=?";
            $stmt = $db->conn->prepare($query);
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo '<p style="position:relative;color:red;left:-300px;">You cannot change this trivia because it is on the trivia of the day!</p>';
            } else {
                $query = "SELECT id FROM trivia_answers WHERE id_trivia=? ORDER BY 1 ASC LIMIT 1";
                $stmt = $db->conn->prepare($query);
                $stmt->bind_param('i', $row['id']);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($answid = $res->fetch_array(MYSQLI_ASSOC)) {
                    $query = "SELECT id FROM trivia_answers WHERE id=?";
                    $stmt = $db->conn->prepare($query);
                    $calc = $answid['id'] + $_POST['update_answer'] - 1;
                    $stmt->bind_param('i', $calc);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $row_answ = $result->fetch_array(MYSQLI_ASSOC);
                        $query = "UPDATE trivia_answers SET answer=? WHERE id=?";
                        $stmt = $db->conn->prepare($query);
                        $stmt->bind_param('si',$_POST['new_update_answer'],$row_answ['id']);
                        $stmt->execute();

                        if(!empty($_POST['new_update_answer_type'])){
                        $query = "UPDATE trivia_answers SET `type`=? WHERE id=?";
                        $stmt = $db->conn->prepare($query);
                        $stmt->bind_param('si',$_POST['new_update_answer_type'],$row_answ['id']);
                        $stmt->execute();
                        }
                    } else {
                        echo '<p style="position:relative;color:red;left:-300px;">That answer does not exist!</p>';
                    }

                }
            }
        }} else {
        echo '<p style="position:relative;color:red;left:-300px;">Complete correctly all inputs!</p>';
    }
}


}

?>
</div>
<div class="edit-acc" id="edit">
    <h1 style="color:#d8d8d8;top:20px;left:20px;position:absolute;">Change account settings</h1>
<form action="" method="POST">
        <label for="new_account_name" style="margin:10px;"><b>New Username</b></label><br>
        <input type="text" placeholder="Enter new Username..." name="new_account_name" style="margin:10px;"><br>
        <label for="new_email" style="margin:10px;"><b>New Email</b></label><br>
        <input type="text" placeholder="Enter new Email..." name="new_email" style="margin:10px;"><br>
        <label for="new_account_password" style="margin:10px;"><b>New Password</b></label><br>
        <input type="password" placeholder="Enter new Password..." name="new_account_password" style="margin:10px;"><br>
        <label for="new_profile_img" style="margin:10px;"><b>New Profile Image</b></label><br>
        <input type="text" placeholder="Enter Path..." name="new_profile_img" style="margin:10px;"><br>
        <label for="new_session_type" style="margin:10px;"><b>Change session type</b></label><br>
        <input type="text" placeholder="Enter session type..." name="new_session_type" style="margin:10px;"><br>
        <button type="submit" name="submitbtn" class="button1"><span>Confirm</span></button>

</form>
<?php 

if (isset($_POST['submitbtn'])) {//modificari la cont
    if (!empty($_POST['new_account_name'])) {
        $query = "UPDATE users SET username=? WHERE id=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('si', $_POST['new_account_name'], $_SESSION['userid']);
        $stmt->execute();
    }
    if (!empty($_POST['new_email'])) {
        $query = "UPDATE users SET email=? WHERE id=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('si', $_POST['new_email'], $_SESSION['userid']);
        $stmt->execute();
    }
    if (!empty($_POST['new_account_password'])) {
        $query = "UPDATE users SET password=? WHERE id=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('si', $_POST['new_account_password'], $_SESSION['userid']);
        $stmt->execute();
    }
    if (!empty($_POST['new_profile_img'])) {
        $query = "UPDATE users SET profile_path=? WHERE id=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('si', $_POST['new_profile_img'], $_SESSION['userid']);
        $stmt->execute();
    }
    if (!empty($_POST['new_session_type'])) {
        $query = "UPDATE users SET session_type=? WHERE id=?";
        $stmt = $db->conn->prepare($query);
        $stmt->bind_param('si', $_POST['new_session_type'], $_SESSION['userid']);
        $stmt->execute();
    }

}

?>
</div>

</div>

<script>


    function changeSetting(evt,tabName){
        var i,tabcontent,tablinks;

        tabcontent = document.getElementsByClassName("tabcontent");
        for(i=0;i<tabcontent.length;i++){
            tabcontent[i].style.display="none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for(i=0;i<tablinks.length;i++){
            tablinks[i].className = tablinks[i].className.replace(" active","");
        }
        document.getElementById(tabName).style.display="block";
        evt.currentTarget.className+=" active";
    }

    document.getElementById("defaultShow").click();
    </script>
</body>

</html>
