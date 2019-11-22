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
    // TODO: prevent plaintext password output
    $insert = $db->prepare("INSERT INTO users VALUES(:name, :pass)");
    $insert->bindParam(':name', $username);
    $insert->bindParam(':pass', password_hash($password, PASSWORD_DEFAULT));
    $insert->execute();

    // todo xss
    print("<p>Login created.</p>");
}

// Try and sign a user up
function signup($username, $password)
{
    // TODO: prevent username reveal

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

        // TODO: sql injection
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
