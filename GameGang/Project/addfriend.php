<?php
require_once './utils/Database.php';
session_start();

$db = new database('localhost', 'root', 'root', 'gamegang', 'utf8');

$id = $_GET['id'];
$query = "INSERT INTO friends (id_user1,id_user2) VALUES(?,?)";
$stmt = $db->conn->prepare($query);
$stmt->bind_param('ii', $_SESSION['userid'], $_GET['id']);
$stmt->execute();
header("Location:profilepage.php?id=$id");
exit();
