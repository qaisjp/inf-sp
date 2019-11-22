<?php

// Reload the page
function reload_page()
{
    print("<script>location.reload()</script>");
}

// Get the databaase
function get_db()
{
    $db = new PDO('sqlite:include/ds_service.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

// Check if user exists
function check_uniqueness($db, $username)
{
    $check = $db->prepare("SELECT * FROM users WHERE username=:name");
    $check->bindParam(':name', $username);
    $result = $check->execute();

    if ($result) {
        while ($row = $check->fetch()) {
            return False;
        }
        return True;
    }

    return False;
}


// Add a user to the database
function add_user($db, $username, $password)
{
    $cert = openssl_pkey_new(array(
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA));
    $privKey = openssl_pkey_get_private($cert);
    openssl_pkey_export($privKey, $strPrivKey, $password);
    $strPubKey = openssl_pkey_get_details($privKey)['key'];
    echo '$strPrivKey:<pre>' . $strPrivKey . '</pre>';
    echo '$strPubKey:<pre>' . $strPubKey . '</pre>';

    $insert = $db->prepare("INSERT INTO users VALUES(:name, :pass, :pub, :priv)");
    $insert->bindParam(':name', $username);
    $insert->bindParam(':pass', password_hash($password, PASSWORD_DEFAULT));
    $insert->bindParam(':pub', $strPubKey);
    $insert->bindParam(':priv', $strPrivKey);

    $insert->execute();

    print("<p>Login created.</p>");
}

// Try and sign a user up
function signup($username, $password)
{
    if (strlen($password) > 72) {
        die("Password max length is 72");
    }

    try {
        $db = get_db();
        if (check_uniqueness($db, $username)) {
            add_user($db, $username, $password);
        } else {
            print("<p>Username is already registered.</p>");
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die("Internal error!");
    }
}

// Destroy the session token
function logout()
{
    if (!session_destroy()) {
        die("failed to destroy session. oh no.");
    }
}

function login($username, $password)
{
    if (check_signed_in()) {
        die("user already logged in");
    }

    try {
        $db = get_db();

        $check = $db->prepare("SELECT * FROM users WHERE username = ?");
        $result = $check->execute(array($username));

        while ($row = $check->fetch()) {
            if (!password_verify($password, $row['password'])) {
                return False;
            }

            $_SESSION["username"] = $row['username'];
            return True;
        }

        return False;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die("Internal error!");
    }
}

// Check if there is a user signed in
function check_signed_in()
{
    return isset($_SESSION['username']);
}
