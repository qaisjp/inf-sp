---
title: Secure Programming - answers2
author: Qais Patankar - s1620208@inf.ed.ac.uk
geometry: margin=2cm
---

1. Identify the vulnerability in the program `vulnerable` and give the name of a CWE which categorises the vulnerability type most closely. Briefly explain the vulnerability and identify in general how and why can an attacker abuse it. (3 marks)

    ----

    There are two relevant CWEs:
    - CWE-134: Use of Externally-Controlled Format String ([link](https://cwe.mitre.org/data/definitions/134.html))
        - Line 25, `strcat(greeting, user);` (where `user` has size **50**, and `greeting` has size **30**)
    - CWE-121: Stack-based Buffer Overflow ([link](https://cwe.mitre.org/data/definitions/121.html))
        - Line 29, `printf(greeting);`

    The CWE that categorises the vulnerability in the **exploit script** most closely is CWE-134. This is because we don't take advantage of the buffer overflow at all.

    An attacker can abuse an externally controlled format string by reading and writing arbitrary memory, allowing them to access or modify data they are not authorised to perform those actions upon.



2. A successful attack should allow an attacker to store the message in `board2` instead of `board1`. Create an exploit script called `exploit` that takes the path of the program as its first argument and launches a successful attack. We will run your program by executing:

    ```
    ./exploit ./vulnerable
    ```

    **Remark: We will only execute your exploit by normal user account**

    It must not output anything other than the output produced by the vulnerable program. You may use any scripting language to write your exploit, provided it runs as described. If you use a high level language, please also attach your original source code in `answers2.pdf`. If your exploit cannot be run as described (files missing or execution errors), no marks will be awarded for this part. We will execute your code in a fresh VM copy of the machine imported into Virtual Box. (7 marks)

    ----

    Done.

3. Please briefly explain your `exploit` script created for the last question and describe how it abuses the `vulnerable` program to force it to store your message in `board2` instead of `board1`. If you use some hard-coded values, please also explain how you got those values. (2 marks)

    ----

    The exploit script is mostly commented to show how it works and how certain values are received. We have explained it below.

    We've hardcoded the address of `board` (`0x80499b0`), encoded as `"\xb0\x99\04\x08"`. This was retrieved by doing the following in gdb:
    - `file vulnerable` - load `vulnerable`
    - `start "" ""`  - start with two empty args & break straight away
    - `find &board,+1024,"board1"` - find the address of the string `"board1"`, starting from address of `board`, searching for `1024` bytes

    We don't actually use that address though. We use `0x80499b5` (encoded appropriately). This is merely the above address slightly tweaked to refer to the character `"1"` instead of the whole `"board1"` string. We call this `board_num_address`.

    The `vulnerable.c` file prefixes your username with `"Hello! "`. This isn't word aligned(?), so we append a couple spaces until it is aligned appropriately. We experimented with difference lengths of "alignment strings" by seeing how our format string (which contained many `"%p"`s) was affected.

    We add our `board_num_address` to the string (four bytes). This means that we have an address someplace on the stack (which we try to later treat as a pointer).

    We append `%9$n`, which means `write $(count-of-bytes-written-so-far) to the pointer at the 9th argument`. The "pointer at the 9th argument" corresponds to the four bytes we wrote earlier - the `board_num_address`.

    Since **only 14 characters** (bytes) have been written so far (`"Hello! "` prefixed by the program, our `"   "` alignment, and our `board_num_address` `\xb5\x99\04\x08`), we need to write an additional **36 characters**.

    This will mean we've written a total of **50 characters**, which corresponds to the ASCII character `"2"`.


    ```
                        *not an accurate representation of memory

    'b'  'o'  'a'  'r'  'd'  '1'  \0
                              ^
                              |
                       ASCII '2' is written here
    ```

    The format string (we supply) - the `user` - in the end looks little like this:

    ```
       \xb5\x99\04\x08%36x%9$n
    ```


4. Provide a patch file that fixes the vulnerability of `vulnerable.c`. The patch file should be named as `question2a.diff`. (1 marks)

    ----

    Done.

5. There is another program `vulnerable2.c` with multiple vulnerabilities. Perform a code review and report up to three _different_ vulnerabilities in this second program.

    For each vulnerability, describe what the problem is, how it might be exploited, and what the possible consequences of an exploit might be. Finally, give a correction to the code to show how it may be fixed.

    You should provide your description and answers in `answers2.pdf` and provide a patch file that fixes your three reported vulnerabilities of `vulnerable2.c`. The patch file should be named as `question2b.diff`. (12 marks)

    ----

    - **Vulnerability 2**
        - **Problem**
            The program uses an externally controlled format string directly with `fprintf`.
        - **Consequences**
            `./a.out "ARTHURCHAN" "123456789" "%x %p"` will write certain values on the stack to `messageboard.txt`. You can use an externally controlled format string (CWE-134) to read and write arbitrary memory.
        - **Correction**
            _Line 28_: `fprintf(file, mess);` should be `fprintf(file, "%s", mess);`

    **Vulnerability 2**
        - **Problem**
            The program uses `gets`. This "function is dangerous and should not be used" (source: gcc). It continuously reads from standard input until a newline is received, but there's no way to limit the number of bytes that are read, so it's prone to buffer overflow.
        - **Consequences**
            You can write arbitrary memory.
        - **Correction**
            _Line 34_ and _Line 36_ use `fgets` instead of `gets`. However, note that `gets` throws away trailing newlines, whereas `fgets` keeps them around. So we allow 1 more character for `user` and `pass` to handle the newline gracefully (but only when we use `fgets`).

            This has the consequence of being slightly messy in output for super-long inputs, but you probably should't be writing something like this in C anyway.

    **Vulnerability 3**
        - **Problem**
            The program does not check the result of `gets`.
        - **Consequence**
            Subsequence `strcmp` calls have undefined behaviour and could even ["contain a good password previously entered by another user"](https://www.informit.com/articles/article.aspx?p=2036582&seqNum=3).
        - **Correction**
            Check the return value. If the return value is 0 (null), quit the program.
