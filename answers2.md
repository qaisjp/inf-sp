---
title: Secure Programming - answers2
author: Qais Patankar - s1620208@inf.ed.ac.uk
geometry: margin=2cm
---

1. Identify the vulnerability in the program `vulnerable` and give the name of a CWE which categorises the vulnerability type most closely. Briefly explain the vulnerability and identify in general how and why can an attacker abuse it. (3 marks)

    ----

    answers

2. A successful attack should allow an attacker to store the message in `board2` instead of `board1`. Create an exploit script called `exploit` that takes the path of the program as its first argument and launches a successful attack. We will run your program by executing:

    ```
    ./exploit ./vulnerable
    ```

    **Remark: We will only execute your exploit by normal user account**

    It must not output anything other than the output produced by the vulnerable program. You may use any scripting language to write your exploit, provided it runs as described. If you use a high level language, please also attach your original source code in `answers2.pdf`. If your exploit cannot be run as described (files missing or execution errors), no marks will be awarded for this part. We will execute your code in a fresh VM copy of the machine imported into Virtual Box. (7 marks)

    ----

    answers

3. Please briefly explain your `exploit` script created for the last question and describe how it abuses the `vulnerable` program to force it to store your message in `board2` instead of `board1`. If you use some hard-coded values, please also explain how you got those values. (2 marks)

    ----

    answers

4. Provide a patch file that fixes the vulnerability of `vulnerable.c`. The patch file should be named as `question2a.diff`. (1 marks)

    ----

    answers

5. There is another program `vulnerable2.c` with multiple vulnerabilities. Perform a code review and report up to three _different_ vulnerabilities in this second program.

    For each vulnerability, describe what the problem is, how it might be exploited, and what the possible consequences of an exploit might be. Finally, give a correction to the code to show how it may be fixed.

    You should provide your description and answers in `answers2.pdf` and provide a patch file that fixes your three reported vulnerabilities of `vulnerable2.c`. The patch file should be named as `question2b.diff`. (12 marks)

    ----

    answers