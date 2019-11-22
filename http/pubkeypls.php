<?php
require_once("include/base.php");
ensure_logged_in();
header('Content-Type: application/x-pem-file');
header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename="key.pem"');
echo get_loggedin_pubkey();