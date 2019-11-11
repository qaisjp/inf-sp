.PHONY: all
all: answers1.pdf answers2.pdf exploit question2a.diff question2b.diff

answers1.pdf: answers1.md
	pandoc answers1.md -o answers1.pdf

answers2.pdf: answers2.md
	pandoc answers2.md -o answers2.pdf

exploit:
	echo "exploit nop"

question2a.diff:
	echo "question2a.diff nop"

question2b.diff:
	echo "question2b.diff nop"

push:
	scp ./exploit/vulnerable.c ./exploit/vulnerable2.c sp1:/home/user/exploit
