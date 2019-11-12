.PHONY: all exploit
all: answers1.pdf answers2.pdf question2a.diff question2b.diff

answers1.pdf: answers1.md
	pandoc answers1.md -o answers1.pdf

answers2.pdf: answers2.md
	pandoc answers2.md -o answers2.pdf

exploit:
	@chmod +x exploit.py
	@scp -q ./exploit.py user@sp1:~/exploit/exploit
	@echo ""
	@rm -f exploit/board1 exploit/board2
	@ssh user@sp1 "cd exploit; ./exploit ./vulnerable"

	@scp -q sp1:/home/user/exploit/board1 sp1:/home/user/exploit/board2 exploit 2>/dev/null || :
	@touch exploit/board1 exploit/board2

	@bat -A exploit/board1
	@bat -A exploit/board2

	@ssh sp1 "rm -f /home/user/exploit/board1 /home/user/exploit/board2"

question2a.diff: exploit/vulnerable.c
	git diff 88eb09265eb15378ee40eb76a16566aed150125a exploit/vulnerable.c > question2a.diff

question2b.diff: exploit/vulnerable2.c
	git diff 88eb09265eb15378ee40eb76a16566aed150125a exploit/vulnerable2.c > question2b.diff

push:
	scp ./exploit/vulnerable.c ./exploit/vulnerable2.c sp1:/home/user/exploit
	ssh sp1 "cd /home/user/exploit; make"
