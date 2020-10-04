<?php
require_once './utils/Database.php';

session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');

$myAnswer = $_GET['a'];
if ($myAnswer == $_SESSION['trivia_correct_answer']) { //verific daca raspunsul pe care l-am ales este si cel corect
    $stmt = $db->conn->prepare("SELECT points FROM trivia WHERE id=?"); //daca da atunci iau din baza de date cate puncte are intrebarea
    $stmt->bind_param('i', $_SESSION['trivia_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $stmt_reward = $db->conn->prepare("INSERT INTO trivia_score (id_trivia,id_user,score) VALUES (?,?,?)");
        $stmt_reward->bind_param('iii', $_SESSION['trivia_id'], $_SESSION['userid'], $row['points']); //ii dau userului punctele pe intrebare
        $finished = $stmt_reward->execute();
        if ($finished) {
            echo $row['points']; //ii arat userului cate puncte a primit
        }
    }
} else {
    echo '0';
}
