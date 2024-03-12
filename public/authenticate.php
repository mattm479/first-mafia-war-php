<?php
session_start();
require_once "../vendor/autoload.php";

use Fmw\Database;

$config = require "../config/application.php";
$db = new Database($config['database']);

$username = htmlspecialchars($_POST['username']);
$password = mysqli_real_escape_string($db, $_POST['password']);

$stmt = $db->prepare("SELECT userid, userpass FROM users WHERE login_name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($userId, $user_password);
$stmt->fetch();

if ($userId == null) {
    header('Refresh: 5; url=index.php');
    die("<br><br><div align=center><h3>Login Error</h3><p>Invalid username or password. Please try again.</p><p><a href=index.php>Return to login</a></p></div>");
}

$isValidPassword = password_verify($password, $user_password);
if (!$isValidPassword) {
    header('Refresh: 5; url=index.php');
    die("<br><br><div align=center><h3>Login Error</h3><p>Invalid password. Please try again.</p><p><a href=index.php>Return to login</a></p></div>");
}

$db->close();

$_SESSION['userId'] = $userId;
$_SESSION['loggedin'] = 1;

header("Location: home.php");
