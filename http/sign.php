<?php
require_once("include/base.php");
ensure_logged_in();
csrf_check();
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die("Bad request method");
}

if (strlen($_POST['message']) > 10000) {
    die('message is too long man');
}

if (isset($_POST['verify'])) {
    $pkeyid = openssl_pkey_get_public("file://".$_FILES['pubkey']['tmp_name']);
    if (!$pkeyid) {
        die("bad pubkey");
    }

    $result = openssl_verify($_POST['message'], hex2bin($_POST['signature']), $pkeyid, OPENSSL_ALGO_SHA512);
    if ($result === 0) {
        echo("signature is incorrect");
    } else if ($result === 1) {
        echo("signature is correct");
    } else {
        echo("something went wrong");
    }

    openssl_free_key($pkeyid);
} else if (isset($_POST['sign'])) {
    $str_pkey = get_loggedin_row()["privkey"];

    $pkeyid = openssl_pkey_get_private($str_pkey, $_POST['password']);

    if (!$pkeyid) {
        die('could not load private key. bad passphrase maybe?');
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
