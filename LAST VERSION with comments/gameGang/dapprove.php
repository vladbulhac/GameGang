<?php
require_once './Controllers/Database.php';
session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');

if (isset($_GET['aid'])) {
    $query = "DELETE FROM trivia_answers WHERE id=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $_GET['aid']);
    $stmt->execute();

    header("Location: myaccount.php?friend_page=1&notif_page=1");
    exit();
}
if (isset($_GET['gid'])) {
    $query = "DELETE FROM games WHERE id=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $_GET['gid']);
    $stmt->execute();

    header("Location: myaccount.php?friend_page=1&notif_page=1");
    exit();
}
if (isset($_GET['qid'])) {
    $query = "DELETE FROM trivia_answers WHERE id_trivia=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $_GET['qid']);
    $stmt->execute();

    $query = "DELETE FROM trivia WHERE id=?";
    $stmt = $db->conn->prepare($query);
    $stmt->bind_param('i', $_GET['qid']);
    $stmt->execute();

    header("Location: myaccount.php?friend_page=1&notif_page=1");
    exit();
}
