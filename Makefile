.PHONY: all exploit question3.diff sshconfig.tar.gz code.tar.gz sync php-log
all: answers1.pdf answers2.pdf answers3.pdf question2a.diff question2b.diff question3.diff

submit: answers1.pdf answers2.pdf question2a.diff question2b.diff answers3.pdf sshconfig.tar.gz question3.diff code.tar.gz
	mkdir -p _submit
	cp exploit.py _submit/exploit
	submit sp cw answers1.pdf answers2.pdf _submit/exploit question2a.diff question2b.diff answers3.pdf sshconfig.tar.gz question3.diff code.tar.gz

answers1.pdf: answers1.md
	pandoc answers1.md -o answers1.pdf

answers2.pdf: answers2.md
	pandoc answers2.md -o answers2.pdf

answers3.pdf: answers3.md
	pandoc answers3.md -o answers3.pdf

exploit:
	@chmod +x exploit.py
	@scp -q ./exploit.py user@sp:~/exploit/exploit
	@echo ""
	@rm -f exploit/board1 exploit/board2
	@ssh user@sp "cd exploit; ./exploit ./vulnerable"

	@scp -q sp:/home/user/exploit/board1 sp:/home/user/exploit/board2 exploit 2>/dev/null || :
	@touch exploit/board1 exploit/board2

	@bat -A exploit/board1
	@bat -A exploit/board2

	@ssh sp "rm -f /home/user/exploit/board1 /home/user/exploit/board2"

question2a.diff: exploit/vulnerable.c
	git diff 88eb09265eb15378ee40eb76a16566aed150125a exploit/vulnerable.c > question2a.diff

question2b.diff: exploit/vulnerable2.c
	git diff 88eb09265eb15378ee40eb76a16566aed150125a exploit/vulnerable2.c > question2b.diff

push1:
	scp ./exploit/vulnerable.c ./exploit/vulnerable2.c sp:/home/user/exploit
	ssh sp "cd /home/user/exploit; make"

push-keys:
	ssh-copy-id sp
	ssh-copy-id user@sp

sshconfig-pull:
	scp user@sp:/etc/ssh/sshd_config sshconfig/

sshconfig-push:
	ssh user@sp "mkdir -p sshconfig"
	scp sshconfig/sshd_config user@sp:sshconfig/sshd_config
	ssh user@sp "sudo sh -c 'cat sshconfig/sshd_config > /etc/ssh/sshd_config; systemctl restart sshd'"

sshconfig-show:
	git diff 930503a30e701c728838ffae4d15d8cb2f53cbe0 sshconfig

sshconfig.tar.gz:
	tar -czf sshconfig.tar.gz -C sshconfig .

hb-run:
	scp openssl-1.0.1f-source/ssl/d1_both.c openssl-1.0.1f-source/ssl/t1_lib.c user@sp:openssl-1.0.1f-source/ssl
	ssh user@sp "cd openssl-1.0.1f-source/; make && make install_sw"
	ssh -tt user@sp "openssl/bin/openssl s_server -key key/key.pem -cert key/cert.pem -accept 12345 -www"

hb-test:
	./heartbleed.py localhost -p 54321

hb-mount:
	mkdir -p ~/mounts/hb
	sshfs user@sp:openssl-1.0.1f-source ~/mounts/hb

hb-unmount:
	fusermount -u ~/mounts/hb

question3.diff:
	git diff 1205086771dfe674fdf68aab019f4c05d306bd23 openssl-1.0.1f-source > question3.diff

php-sync:
	while true; do inotifywait -e close_write -r http | while read dir event f; do scp "$$dir$$f" user@sp:"/srv/$$dir$$f"; done; done

php-log:
	ssh user@sp -ttt "tail -f /var/log/httpd/error_log"

php-sync-httpd:
	scp httpd.conf sp:/etc/httpd/conf/httpd.conf
	scp php.ini sp:/etc/php/php.ini
	ssh sp "systemctl restart httpd"

code.tar.gz:
	tar -czf code.tar.gz http httpd.conf php.ini

php-mount:
	mkdir -p ~/mounts/php
	sshfs user@sp:/srv/http ~/mounts/php

php-unmount:
	fusermount -u ~/mounts/php

php-sqlite:
	[ -f ~/mounts/php/include/ds_service.db ] && sqlite3 ~/mounts/php/include/ds_service.db
