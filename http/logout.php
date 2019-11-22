<?php
require_once("include/base.php");
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die("Bad request method");
}
csrf_check();
ensure_logged_in();
logout();
header("Location: /");