<?php
require_once './Controllers/Database.php';
session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');

$id = $_GET['id'];
$query = "DELETE FROM friends WHERE id_user1=? AND id_user2=?";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('ii', $_SESSION['userid'], $_GET['id']);
$stmt->execute();
header("Location: profilepage.php?id=$id");
exit();
