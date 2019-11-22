<?php
require_once("include/base.php");

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die("Bad request method");
}

$username = $_POST["username"];
$password = $_POST["password"];

if (isset($_POST["signup"])) {
    if (signup($username, $password)) {
        print("you're all signed up! well done.");
        print("<a href='/'>go back and try logging in</a>");
    }
    die();
}

if (!login($username, $password)) {
    print("invalid username or password - go back and try again <br>");
} else {
    print('well done. success.');
}
print("<a href='/'>back</a>");
