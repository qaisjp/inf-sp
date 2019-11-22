<?php
require_once("include/base.php");

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die("Bad request method");
}

$username = $_POST["username"];
$password = $_POST["password"];

login($username, $password);
