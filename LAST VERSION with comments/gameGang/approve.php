<?php
require_once './Controllers/Database.php';
session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');

if (isset($_GET['aid'])) {
    $query = "UPDATE trivia_answers SET approved=1 WHERE id=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $_GET['aid']);
    $stmt->execute();

    header("Location: myaccount.php?friend_page=1&notif_page=1");
    exit();
}
if (isset($_GET['gid'])) {
    $query = "UPDATE games SET approved=1 WHERE id=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $_GET['gid']);
    $stmt->execute();

    header("Location: myaccount.php?friend_page=1&notif_page=1");
    exit();
}
if (isset($_GET['qid'])) {
    $query = "UPDATE trivia SET approved=1 WHERE id=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $_GET['qid']);
    $stmt->execute();

    header("Location: myaccount.php?friend_page=1&notif_page=1");
    exit();
}
