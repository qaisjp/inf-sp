<?php
require_once("include/base.php");
ensure_logged_in();
logout();
header("Location: /");