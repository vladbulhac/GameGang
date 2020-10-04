<?php
require_once './Controllers/Database.php';

session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');

if (isset($_SESSION['played_trivia'])) {
    header("Location: index.php?trivia=playedToday");
} else {
    if (isset($_SESSION['userid'])) {
        $_SESSION['played_trivia'] = 1;
    }

}

?><!DOCTYPE html>
<html>
<head>
<title>Trivia Game</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="style/trivia-style.css" rel="stylesheet" type="text/css">
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

      </ul>
   </nav>
</div>
</header>

<body>



<div class="wrapper">


   <div class="trivia-box">
        <div class="question">
             <h3>Trivia of the day</h3>
             <?php
$query = "SELECT question FROM trivia WHERE id=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['trivia_id']);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_array(MYSQLI_ASSOC)) { //afisez intrebarea
    echo '
    <h2>' . $row['question'] . '</h2>
    ';
}
?>
             <p id="timer"></p>
        </div>
        <form action="" method="POST" class="answers">

<?php
$query = "SELECT id,answer,type FROM trivia_answers WHERE id_trivia=? AND approved=1";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('i', $_SESSION['trivia_id']);
$stmt->execute();
$res = $stmt->get_result();
$counter = 1;
while ($row = $res->fetch_array(MYSQLI_ASSOC)) { //afisez variantele de raspuns si retin in session varianta corecta de raspuns
    if ($row['type'] == 'correct') {
        $_SESSION['trivia_correct_answer'] = $row['answer'];
    }
    echo '
 <button type="button" class="answ' . $counter . '" name="' . $counter . '" value="' . $row['answer'] . '" onclick="getVal(this);">' . $row['answer'] . '</button>
    ';
    $counter++;
}
?>

</form>



</div>

</body>

<script>
var timeleft = 15;
var correctAnswer='<?php if (isset($_SESSION['trivia_correct_answer'])) {
    echo $_SESSION['trivia_correct_answer'];
}
?>';
var checkAnswerTimer = setInterval(function(){
  document.getElementById("timer").innerHTML = timeleft;
  timeleft -= 1;
  if(timeleft <= 0){//deschidem cu ajax si verificam raspunsul
    clearInterval(checkAnswerTimer);
    document.getElementById("timer").innerHTML = "Finished";

    if(window.XMLHttpRequest){
        xhr=new XMLHttpRequest();
    }
    else
    if(window.ActiveXObject){
        xhr=new ActiveXObject("Microsoft.XMLHTTP");
    }

   xhr.onreadystatechange=function(){
  if(this.readyState==4 && this.status==200){
      var response=this.responseText;
      if(response==0){

       if(button_name==0){//cazul in care nu am ales nicio varianta de raspuns
        buttons=document.getElementsByTagName("button");
        for(i=0;i<buttons.length;i++)
     if(buttons[i].value==correctAnswer){
        buttons[i].style.backgroundColor="green";
         buttons[i].style.color="white";
     break;
     }
       }else{

     document.getElementsByName(button_name)[0].style.backgroundColor="red";  //rosu butonul gresit si verde butonul bun
     document.getElementsByName(button_name)[0].style.color="white";
     buttons=document.getElementsByTagName("button");
     for(i=0;i<buttons.length;i++)
     if(buttons[i].value==correctAnswer){
        buttons[i].style.backgroundColor="green";  //rosu butonul gresit si verde butonul bun
         buttons[i].style.color="white";
     break;
     }
       }
     var redirect=3;
     setInterval(function(){
         redirect-=1;
         if(redirect<=0){
             window.location.href="index.php";
         }
     },1000);

      }else{//daca am raspuns corect
        document.getElementsByName(button_name)[0].style.backgroundColor="green";  //verde butonul bun
     document.getElementsByName(button_name)[0].style.color="white";
     var redirect=5;

     setInterval(function(){
         redirect-=1;
         if(redirect==1){
               alert('You earned '+response+' points');
         }
         if(redirect<=0){
             window.location.href="index.php";
         }
     },1000);
      }
  }
   };

   xhr.open('GET','checkTrivia.php?a='+answer);
   xhr.send();
  }
}, 1000);

var answer=0;
var button_name=0;
function getVal(button){
    answer=button.value;
    button_name=button.name;
}


    </script>


<html>