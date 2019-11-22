<?php
require_once("include/base.php");

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die("Bad request method");
}

$username = $_POST["username"];
$password = $_POST["password"];

if (!login($username, $password)) {
    print("invalid username or password - go back and try again <br>");
    print("<a href='/'>back</a>");
}
