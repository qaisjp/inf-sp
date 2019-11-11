.PHONY: all exploit
all: answers1.pdf answers2.pdf exploit question2a.diff question2b.diff

answers1.pdf: answers1.md
	pandoc answers1.md -o answers1.pdf

answers2.pdf: answers2.md
	pandoc answers2.md -o answers2.pdf

exploit:
	@chmod +x exploit.py
	@scp ./exploit.py user@sp1:~/exploit/exploit
	@echo ""
	@ssh user@sp1 "cd exploit; ./exploit ./vulnerable"
	@echo ""
	@echo "board1:"
	@echo "-------"
	@ssh sp1 "touch /home/user/exploit/board1; cat /home/user/exploit/board1; rm /home/user/exploit/board1"

	@echo ""
	@echo "board2:"
	@echo "-------"
	@ssh sp1 "touch /home/user/exploit/board2; cat /home/user/exploit/board2; rm /home/user/exploit/board2"

question2a.diff: exploit/vulnerable.c
	git diff 88eb09265eb15378ee40eb76a16566aed150125a exploit/vulnerable.c > question2a.diff

question2b.diff: exploit/vulnerable2.c
	git diff 88eb09265eb15378ee40eb76a16566aed150125a exploit/vulnerable2.c > question2b.diff

push:
	scp ./exploit/vulnerable.c ./exploit/vulnerable2.c sp1:/home/user/exploit
