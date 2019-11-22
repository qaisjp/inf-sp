<?php
require_once("include/base.php");
ensure_logged_in();

if (strlen($_POST['message']) > 10000) {
    die('message is too long man');
}

if (isset($_POST['verify'])) {
    echo 'verify';
} else if (isset($_POST['sign'])) {
    $str_pkey = get_loggedin_row()["privkey"];

    $pkeyid = openssl_pkey_get_private($str_pkey, $_POST['password']);

    if (!$pkeyid) {
        openssl_free_key($pkeyid);
        die('bad passphrase');
    }

    if (openssl_sign($_POST['message'], $signature, $pkeyid, OPENSSL_ALGO_SHA512)) {
        echo '<pre>' . bin2hex($signature) . '</pre>';
    } else {
        echo 'something went wrong: ' . htmlspecialchars(openssl_error_string());
    }
    openssl_free_key($pkeyid);
} else {
    die('que?');
}
