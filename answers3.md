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

Here are a list of general notes and security mitigations:

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

	(If there was a file in there that performs an action, this prevents it from being run.)

3. We've moved `db/ds_service.db` to `include/ds_service.db`, to prevent it from being downloaded. If someone downloads it they would have everyone's data including their passwords. This is bad! Protection is provided by blocking the include folder.

	A better fix would be to move it outside a folder that httpd serves.

4. Remove own session code.

	We've removed the code that tries to recreate PHP sessions. It is bad practice to reinvent the wheel, and in this case, it was implemented quite poorly.

	One example of why it's bad is because it depends directly on md5 hashes. See https://stackoverflow.com/questions/22140204/why-md5240610708-is-equal-to-md5qnkcdzo and https://www.whitehatsec.com/blog/magic-hashes/ for info on this.

	We'll use PHP's inbuilt session feature instead.