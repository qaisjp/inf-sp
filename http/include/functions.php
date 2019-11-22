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
    $insert->bindParam(':pass', $password);
    $insert->execute();

    // todo xss
    print("<p>Created login for '{$username}'.</p>");
}

// Try and sign a user up
function signup($username, $password)
{
    // TODO: prevent username reveal

    try {
        $db = get_db();
        if (check_uniqueness($db, $username)) {
            add_user($db, $username, $password);
        } else {
            // todo xss
            print("<p>Username '{$username}' is already registered.</p>");
        }
    } catch (PDOException $e) {
        // todo information disclosure
        print($e->getMessage());
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
    logout();

    try {
        $db = get_db();

        // TODO: sql injection
        $check = $db->prepare("SELECT * FROM users WHERE username='" . $username . "' AND password='" . $password . "'");
        $result = $check->execute();

        while ($row = $check->fetch()) {
            $_SESSION["username"] = $row['username'];
            return True;
        }

        return False;
    } catch (PDOException $e) {
        // todo information disclosure
        print($e->getMessage());
    }
}

// Check if there is a user signed in
function check_signed_in()
{
    return isset($_SESSION['username']);
}
