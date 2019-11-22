---
title: Secure Programming - answers3
author: QP - s1620208@inf.ed.ac.uk
geometry: margin=2cm
---

# 3.1 OpenSSH configuration

Here are my entire changes to `/etc/ssh/sshd_config`:

```diff
index 1d2180f..31acc81 100644
--- a/etc/ssh/sshd_config
+++ b/etc/ssh/sshd_config
@@ -68,7 +68,7 @@ AuthorizedKeysFile    .ssh/authorized_keys
 #IgnoreRhosts yes

 # To disable tunneled clear text passwords, change to no here!
-#PasswordAuthentication yes
+PasswordAuthentication no
 #PermitEmptyPasswords no

 # Change to no to disable s/key passwords
@@ -128,3 +128,9 @@ Subsystem   sftp    /usr/lib/ssh/sftp-server
 #      X11Forwarding no
 #      AllowTcpForwarding no
 #      ForceCommand cvs server
+
+# Allow only the specified users
+AllowUsers user
+
+# Allow only pub key
+AuthenticationMethods publickey
```

The first change `PasswordAuthentication no` prevents password authentiction from being used by all users.

The second change, `AllowUsers user` allows _only_ the user named **`user`** to login via SSH.

The final change, `AuthenticationMethods publickey`, only allows "ssh keys" (private and public keys) to be used for authentication. This alone will prevent password authentication, so you could say that `PasswordAuthentication no` is unnecessary.

\pagebreak

# 3.2 Fun with Heartbleed

**a) Briefly explain how an attacker can exploit the Heartbleed bug. What are the possible consequence of such an attack? (2 marks)**

It can be exploited by requesting a client or server to send back a specific string as a heartbeat. The heartbeat request includes: a string to send back + the size of that string.

The given size of the string (in the request) is not validated, so more bytes than the actual size of the string can be sent back.

This is a buffer overread, cwe-126. The common consequences of cwe-126 (and therefore this attack) include reading arbitrary values in memory. This includes the private key or any other secrets that may be in memory.

**b) Try to review the code and fix the Heartbleed bug. Briefly describe your fix in answers3.pdf**.

```diff
diff --git a/openssl-1.0.1f-source/ssl/t1_lib.c b/openssl-1.0.1f-source/ssl/t1_lib.c
index ec6578a..7c04207 100644
--- a/openssl-1.0.1f-source/ssl/t1_lib.c
+++ b/openssl-1.0.1f-source/ssl/t1_lib.c
@@ -2562,6 +2562,8 @@ tls1_process_heartbeat(SSL *s)
 	hbtype = *p++;
 	n2s(p, payload);
 	pl = p;
+	if (1 + 2 + payload + 16 > s->s3->rrec.length)
+		return 0;

 	if (s->msg_callback)
 		s->msg_callback(0, s->version, TLS1_RT_HEARTBEAT,
```

My patch to fix TLS, copied above, modifies `ssl/t1_lib.c`.

The patch checks to make sure that `heartbeat_type + heartbeat_length + payload_size + padding` is greater than the actual record length, and if so, discard.

The script at https://gist.github.com/eelsivart/10174134 was used to test my fix to TLS. The fix to DTLS has not been tested, so the above patch is simply reapplied (at the correct lines) to `ssl/d1_both.c` under the educated guess that it will Just Work.

----

Note: I have put my coursework under `git` version control are I am using it to generate my patch files. This is why you see unrecognisable commit hashes!

Makefile:
```make
question3.diff:
	git diff 1205086771dfe674fdf68aab019f4c05d306bd23 openssl-1.0.1f-source > question3.diff
```

\pagebreak

# 3.3 Digital Signature Service (35 marks)

**setting stuff up**

- `ds_service.db` must have write permissions from group `http` so that it can be written to.

	```bash
	chmod 664 ds_service.db
	sudo chgrp http ds_service.db
	```

- table `users` needs modification. run the following sql statements as you like to get things set up:

	```sql
	alter table users add pubkey blob;
	alter table users add privkey blob;
	```

- `/etc/httpd/conf/httpd.conf` should be updated with our `httpd.conf`. it blocks access to the `include` folder by adding the following node:

	```xml
	<Location "/include">
		Order Allow,Deny
		Deny from all
	</Location>
	```

- `/etc/php/php.ini` should be updated with our `php.init`. it enables the `openssl` extension.

	```diff
	diff --git a/php.ini b/php.ini
	index a2f3557..bed3dda 100644
	--- a/php.ini
	+++ b/php.ini
	@@ -885,7 +885,7 @@ extension=gettext.so
	;extension=mysql.so
	;extension=odbc.so
	;zend_extension=opcache.so
	-;extension=openssl.so
	+extension=openssl.so
	;extension=pdo_mysql.so
	;extension=pdo_odbc.so
	;extension=pdo_pgsql.so
	```

**how features were implemented**

1. signup and login uses `password_hash` and `password_verify` with the default settings. (bcrypt, cost 10). the user's password is used as the private key passphrase when signing up. both of these are stored as strings in the `users` table, with column type `blob`.

	an attacker who steals our database could then decrypt data that was encrypted with these private keys, but only if they manage to crack the passwords. a better solution would be require the user to specify their own passphrase that is used when performing operations (one that isn't stored on disk.)

2. public keys are public, so they are stored verbatim in the database.

	so when exporting the private key, we just read it from database and spit it out.

	```php
	<?php
	require_once("include/base.php");
	ensure_logged_in();
	header('Content-Type: application/x-pem-file');
	header("Cache-Control: no-store, no-cache");
	header('Content-Disposition: attachment; filename="key.pem"');
	echo get_loggedin_pubkey();
	```

3. digital signing

	the user also needs to provide their password since the priv key's passphrase is the user's password. a better alternative would be to ask the user for a passphrase when they register, so that we don't reuse the password as a passphrase.

	If an attacker got passwords, and managed to crack the passwords, they would then be able to decode the private keys. bad! but a risk we are going to take for this webapp. still, an attacker could try to update our code with one that holds onto the password in plaintext, but this is one of the most dangerous scenarios as they could do literally anything.

	an even better alternative would be to send the priv key to the user and let them insert the passphrase (see no. 1 about custom passphrases) so that we (well, our backend) does not see passphrases at all. but since we should "never allow the users export or access their private key", we shall not implement this.

	code for this can be found at the bottom of `sign.php`.

	we picked sha512 as the signature algo since the default of sha1 is broken: https://shattered.io/


4. todo

**security mitigations + improvements**

Here are an almost comprehensive list of security mitigations that my code includes (or stuff that could be improved):

1. We have deleted `include/.admin.php`. CWE-200. Example CVE: https://www.cvedetails.com/cve/CVE-2002-2247/

	It is a common mistake to leave `phpinfo()` out in the wild. Leaving this there would disclose too much information like the PHP version, OS version, and lots of other stuff. This can then be used in helping someone perform an attack (as they would know if we're using an outdated PHP version, etc.)

	Solution: prevent `phpinfo()` from being run.

2. We have added the following entry to `httpd.conf`:

	```xml
	<Location "/include">
		Order Allow,Deny
		Deny from all
	</Location>
	```

	This ensures that code only meant for inclusion cannot be run directly by a user. This prevents unintended behaviour from occurring.

	(If there was a file in there that, when executed directly, performs an action we wouldn't want a user to perform, this prevents it from being run directly by the user.)

3. We've moved `db/ds_service.db` to `include/ds_service.db`, to prevent it from being downloaded. If someone downloads it they would have everyone's data including their passwords. This is bad! Protection is provided by blocking the include folder.

	A better fix would be to move it outside a folder that httpd serves.

4. Remove own session code.

	We've removed the code that tries to recreate PHP sessions. It is bad practice to reinvent the wheel, and in this case, it was implemented quite poorly.

	One example of why it's bad is because it depends directly on md5 hashes. See https://stackoverflow.com/questions/22140204/why-md5240610708-is-equal-to-md5qnkcdzo and https://www.whitehatsec.com/blog/magic-hashes/ for info on this.

	We'll use PHP's inbuilt session feature instead.

	Relevant changes include:

	```diff
     // Destroy the session token
     function logout()
     {
    -    setcookie("username", "", time()-3600);
    -    setcookie("session", "", time()-3600);
	+    if (!session_destroy()) {
	+        die("failed to destroy session. oh no.");
	+    }
     }
	```

	and

	```diff
	- create_token($row['username']);
	+ _SESSION["username"] = $row['username'];
	```

	and the removal of `create_token` and `check_token`.

5. Log error messages to console instead of to user

	fixes CWE-200 Information disclosure

	patch:

	```diff
	diff --git a/http/include/functions.php b/http/include/functions.php
	index 5df9081..816a336 100644
	--- a/http/include/functions.php
	+++ b/http/include/functions.php
	@@ -59,8 +59,8 @@ function signup($username, $password)
				print("<p>Username '{$username}' is already registered.</p>");
			}
		} catch (PDOException $e) {
	-        // todo information disclosure
	-        print($e->getMessage());
	+        error_log($e->getMessage());
	+        die("Internal error!");
		}
	}
	```

	(applied wherever necessary)

	Prevents information from being leaked when an error occurs

6. fix sql injection on login

	CWE-89: https://cwe.mitre.org/data/definitions/89.html

	```diff
	diff --git a/http/include/functions.php b/http/include/functions.php
	index 816a336..9254e81 100644
	--- a/http/include/functions.php
	+++ b/http/include/functions.php
	@@ -82,8 +82,8 @@ function login($username, $password)
			$db = get_db();

			// TODO: sql injection
	-        $check = $db->prepare("SELECT * FROM users WHERE username='" . $username . "' AND password='" . $password . "'");
	-        $result = $check->execute();
	+        $check = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
	+        $result = $check->execute(array($username, $password));

			while ($row = $check->fetch()) {
				$_SESSION["username"] = $row['username'];
	```

	you can provide something malicious to log in as any user or perform any sql command. see the following lab: https://www.inf.ed.ac.uk/teaching/courses/sp/2019/labs/lab2/

	Example username to use log in as `david` without needing the correct password: `david' -- ` (don't forget trailing space after the two dashes)

	fix works by using question mark parameters, which includes and escapes values for us. see https://www.php.net/manual/en/pdo.prepare.php#refsect1-pdo.prepare-examples

7. fix xss

	CWE-79: https://cwe.mitre.org/data/definitions/79.html

	```diff
	diff --git a/http/include/functions.php b/http/include/functions.php
	index 9254e81..a6ad0ee 100644
	--- a/http/include/functions.php
	+++ b/http/include/functions.php
	@@ -42,7 +42,7 @@ function add_user($db, $username, $password)
		$insert->execute();

		// todo xss
	-    print("<p>Created login for '{$username}'.</p>");
	+    print("<p>Login created.</p>");
	}

	// Try and sign a user up
	@@ -55,8 +55,7 @@ function signup($username, $password)
			if (check_uniqueness($db, $username)) {
				add_user($db, $username, $password);
			} else {
	-            // todo xss
	-            print("<p>Username '{$username}' is already registered.</p>");
	+            print("<p>Username is already registered.</p>");
			}
		} catch (PDOException $e) {
			error_log($e->getMessage());
	```

	if you are printing variables that come from or are derived from user input, the programmer should escape the text so that an xss attack cannot be performed.

	this is commonly done in php via a function called `htmlspecialchars`.

	without that function, a username `<script>alert("xss");</script>` would be directly transcluded into the html page, resulting in an alert popping up in the users browser (via javascript running in their browser). a malicious user (our attacker, Eve) could instead silently send cookies to Eve's website. or run a bitcoin miner on that page.

	so above, instead of `... Username '{$username}' is ...`

	you would do `... Username '{htmlspecialchars($username)}' is ...`

	however, the "solution" we applied here is to simply not print out the username.

8. plaintext passwords

	plaintext passwords are being stored in the database. this is bad practice as it means it is super easy for anyone with access to the db to see someone's password. it also means that if the db is stolen, the attacker has access too.

	the solution here is to store the output of `password_hash` as the password. and to verify using `password_verify`.

	we also limit the password length to 72 (when registering accounts) as the default `password_hash` uses bcrypt, which truncates to 72. it is better to fail and notify the user about this, instead of silently truncating.

	password_hash/verify with bcrypt automatically salts and hashes our password without us needing to worry too much about the intricacies. the default cost of 10 is fine.

	```diff
	diff --git a/http/include/functions.php b/http/include/functions.php
	index a6ad0ee..395a375 100644
	--- a/http/include/functions.php
	+++ b/http/include/functions.php
	@@ -38,7 +38,7 @@ function add_user($db, $username, $password)
		// TODO: prevent plaintext password output
		$insert = $db->prepare("INSERT INTO users VALUES(:name, :pass)");
		$insert->bindParam(':name', $username);
	-    $insert->bindParam(':pass', $password);
	+    $insert->bindParam(':pass', password_hash($password, PASSWORD_DEFAULT));
		$insert->execute();

		// todo xss
	@@ -50,6 +50,10 @@ function signup($username, $password)
	{
		// TODO: prevent username reveal

	+    if (strlen($password) > 72) {
	+        die("Password max length is 72");
	+    }
	+
		try {
			$db = get_db();
			if (check_uniqueness($db, $username)) {
	@@ -81,10 +85,14 @@ function login($username, $password)
			$db = get_db();

			// TODO: sql injection
	-        $check = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
	-        $result = $check->execute(array($username, $password));
	+        $check = $db->prepare("SELECT * FROM users WHERE username = ?");
	+        $result = $check->execute(array($username));

			while ($row = $check->fetch()) {
	+            if (!password_verify($password, $row['password'])) {
	+                return False;
	+            }
	+
				$_SESSION["username"] = $row['username'];
				return True;
			}
	```
9. don't display errors

	in production the `ini_set('display_errors', 'On');` line should be removed or turned off to prevent leakage of code or other information to the user

	it theoretically code (depending on the type of error or the error message etc) expose keys in the environment and leak code

10. lazy basic csrf protection is added too. my logout feature had to be modified to use a logout form instead of a logout link.

	csrf is cross site request forgery. it means that Eve (attacker) could make a webpage with a form pointing to Alice's website, and send that webpage to Bob. Bob goes on Eve's webpage which automatically submits the form to Alice's website, doing something evil. To prevent this from happening Alice's csrf token is added Alice's form. the token cannot be provided by Eve's backend, only Alice's backend. Alice's backend verifies the token, so this makes sure that forms originate from Alice's frontend. (this is not a technique to prevent botting, however, since bots can simulate an entire session.)

	a good example of why csrf on **logout** is necessary can be found here: https://security.stackexchange.com/a/95569

	```php
	// must only be called once per page
	function csrf_check()
	{
		$t = $_SESSION['csrf_token'];
		unset($_SESSION['csrf_token']);
		if (!isset($_POST['csrf_token'])) {
			die("csrf missing");
		}
		if ($_POST['csrf_token'] !== $t) {
			die("csrf no match");
		}
	}

	function csrf_set() {
		// From https://gist.github.com/ziadoz/3454607#file-index-php-L7
		if (!isset($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token'] = base64_encode(openssl_random_pseudo_bytes(32));
		}
	}

	function csrf_input() {
		return "<input name=csrf_token type='hidden' value='" . $_SESSION['csrf_token'] . "'>";
	}
	```
