---
title: Secure Programming - answers3
author: QP - s1620208@inf.ed.ac.uk
geometry: margin=2cm
---

# 3.1 OpenSSH configuration

Here are my entire changes to sshd_config:

```
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
