---
title: Secure Programming - answers1
author: Qais Patankar - s1620208@inf.ed.ac.uk
geometry: margin=2cm
---

Consider these CVEs, [CVE-2019-11091], [CVE-2018-12126], [CVE-2018-12127] and [CVE-2018-12130].

1. Security researchers consider RIDL and Fallout inspired by previous speculative execution attacks, including
Meltdown and Spectre. Study the CVE entries and related documentations for RIDL and Fallout, in your
own words briefly describe what is a speculative execution attack and the similarities and differences of
RIDL and Fallout comparing to Meltdown and Spectre. **(4 marks)**

    ----

    A speculative execution attack is....

    Compare similarities and differences.


2. There are four CVE entry describing RIDL and Fallout, please identify which of them is (are) describing
RIDL and which of them is (are) describing Fallout. **(2 marks)**

    ----

    - RIDL is described by: CVE-2018-12127, CVE-2018-12130, and CVE-2019-11091
    - Fallout is described by: CVE-2018-12126

3. Identify the Common Weaknesses (using CWEs) for these two vulnerabilities. Also identify which scope in
the CIA triad has been violated by the common weaknesses and why. **(4 marks)**

    ----

    The CWEs for these vulnerabilities are:
    - CWE-200 Information Exposure

    It has violated **Confidentiality** in the CIA traid. This is because sensitive information can be accessed by those not authorised to have access.


4. Identify the possible consequences of these vulnerabilities and how an attacker can make use of the
consequences. **(4 marks)**

    ----

    answers

5. These two vulnerabilities are considered to be “hardware” vulnerabilities. Briefly discuss the difference
between hardware vulnerabilities and software vulnerabilities and how you might draw a distinction. To
help your explanation, give an example of one software vulnerability and one other hardware vulnerability
that are listed in the NVD (https://nvd.nist.gov) and occurred in the last two years. **(4 marks)**

    Remark: No marks will be given if you give an example of RIDL/Fallout/Meldown/Spectre.

    ----

    Hardware vulnerabilities exist in the hardware and cannot be retroactively fixed - they can only be fixed by hardware manufacturers by producing new or modified versions of hardware with the vulnerabilities resolved. This requires users to purchase newer hardware (or for manufacturers to recall hardware & supply fixed versions).

    Software vulnerabilities _can_ be fixed retroactively via software security updates. Software security updates ordinarily do not require the user to purchase new software. (But it depends on whether users get "free security updates".)



6.  > “RIDL and Fallout are so dangerous. After I heard about these vulnerabilities, I did a full scan of
    > my computer by the latest anti-virus software and installed all application updates. The anti-virus
    > protection does not find any problems. I have also installed a personal firewall in my computer. So I
    > strongly believe that my computer should be secure enough to defend RIDL and Fallout attacks.”
    >
    > \- By Mr. Super Secure

    State your opinion and discuss whether you agree or disagree with Mr. Super Secure’s words. Provide
some reasons to support your opinion, explaining the role of PC security software mentioned. **(5 marks)**

    ----

    I disagree with Mr. Super Secure.

    Software updates might be able to work around these vulnerabilities (at the cost of performance). It is possible for virus scanners to block *known* programs that make use of the vulnerability.

    But, in the general case, since RIDL and Fallout are hardware vulnerabilities (not software vulnerabilities) updating software and scanning for viruses is not a foolproof solution. The only sure way to be protected is to get a new processor - one which is not vulnerable.

    A firewall cannot protect you from RIDL and Fallout _specifically_. It might be able to prevent certain software from being abused (via exposure to the Internet, which then in turn might use RIDL and Fallout), but it does not protect from these hardware vulnerabilities in itself.

    Additionally, a machine can be attacked via these hardware vulnerabilities even if they do not share a network - for example, take a virtual machine (not connected to any network, "closed-off") on a cloud platform. Any other virtual machine on the platform, on the same host machine, could leverage RIDL or Fallout to attack the "closed-off" machine. (Hence a firewall won't protect you!)

7. Apart from installing the above mentioned security software, please describe two possible measures to protect your computer from RIDL and Fallout attacks. **(2 marks)**

    ----

    1. You should get a newer processor that has these vulnerabilities fixed. (Or an AMD processor, since those are unaffected.)
    2. or... disable hyper-threading on your Intel processor, which has an associated performance hit, and still does not protect against MDS.

[CVE-2019-11091]: https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2019-11091
[CVE-2018-12126]: https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2018-12126
[CVE-2018-12127]: https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2018-12127
[CVE-2018-12130]: https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2018-12130