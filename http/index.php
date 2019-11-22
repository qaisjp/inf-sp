<?php
require_once("include/base.php");
echo $_SESSION["TEST"];
// $_SESSION["TEST"] = "ok";
?>

<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Super Secure Digital Signature Service</title>

  <!-- Bootstrap -->
  <link href="css/bootstrap.min.css" rel="stylesheet">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
  <?php
  ini_set('display_errors', 'On');
  require_once('include/functions.php');
  ?>
  <div class="jumbotron">
    <h1>Super Secure Digital Signature Service</h1>
  </div>

  <div class="container">
    <?php

    if (check_signed_in()) {
      print 'Logged in as ' . $_SESSION["username"];
      print '<br><a href="/logout.php">logout</a><br>';
      print '<br><a href="/pubkeypls.php">download pub key</a><br>';

      ?>
      <br>
      <form name="login" class="form-horizontal" action="/sign.php" method="post">
        <fieldset>
          <legend>sign key</legend>
          <div class="control-group"> <label class="control-label" for="message">message</label>
            <div class="controls"> <textarea id="message" name="message" type="text" placeholder="message" class="form-control" required></textarea></div>
          </div>
          <div class="control-group"> <label class="control-label" for="password">Passphrase</label>
            <div class="controls"> <input id="password" name="password" type="password" placeholder="this will be your password" class="form-control" required></div>
          </div>
          <div class="control-group"> <label class="control-label" for="createaccount"></label>
            <div class="controls"> <button id="signup" name="sign" type="submit" value="signup" class="btn btn-default">Sign with passhrase</button> </div>
          </div>
        </fieldset>
      </form>
      <br>

      <form name="login" class="form-horizontal" action="/verify.php" method="post">
        <fieldset>
          <legend>verify</legend>
          <div class="control-group"> <label class="control-label" for="message">message</label>
            <div class="controls"> <textarea id="message" name="message" type="text" placeholder="message" class="form-control" required></textarea></div>
          </div>
          <div class="control-group"> <label class="control-label" for="signature">signature</label>
            <div class="controls"> <textarea id="signature" name="signature" type="text" placeholder="signature" class="form-control" required></textarea></div>
          </div>
          <div class="control-group"> <label class="control-label" for="pubkey">public key</label>
            <div class="controls"> <input id="pubkey" name="pubkey" type="file" required></div>
          </div>
          <div class="control-group"> <label class="control-label" for="createaccount"></label>
            <div class="controls"> <button name="verify" type="submit" value="signup" class="btn btn-default">verify</button> </div>
          </div>
        </fieldset>
      </form>

    <?php
    } else {
      print "Not signed in";

      print '<form name="login" class="form-horizontal" action="/login.php" method="post">';
      print '  <fieldset>';
      print '    <legend>Login or sign up</legend>';
      print '    <div class="control-group">';
      print '      <label class="control-label" for="username">User name</label>';
      print '      <div class="controls">';
      print '        <input id="username" name="username" type="text" placeholder="username" class="form-control" required="">';
      print '      </div>';
      print '    </div>';
      print '    <div class="control-group">';
      print '      <label class="control-label" for="password">Password</label>';
      print '      <div class="controls">';
      print '        <input id="password" name="password" type="password" placeholder="password" class="form-control" required="">';
      print '      </div>';
      print '    </div>';
      print '    <div class="control-group">';
      print '      <label class="control-label" for="createaccount"></label>';
      print '      <div class="controls">';
      print '        <button id="signup" name="signup" type="submit" value="signup" class="btn btn-default">Sign up</button>';
      print '        <button id="login" name="login" type="submit" value="login" class="btn btn-default">Log in</button>';
      print '      </div>';
      print '    </div>';
      print '  </fieldset>';
      print '</form>';
    }
    ?>
  </div>

  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="js/bootstrap.min.js"></script>
</body>

</html>