
Recent Debian and Debian-like Linux distributions include a *conf.d*-style mechanism for managing
network interfaces at `/etc/network/interfaces.d`, and new directives for the main `interfaces` file,

 - `source <fileglob>`
 - `source-directory <dirglob>`

typically used to include configuration snippets from places like the new `interfaces.d`.

Unfortunately, many network tools and utilities (e.g. guessnet-ifupdown) are not yet prepared to consider these directives when doing whatever they're supposed to be doing.  It seems like most tools actually parse `interfaces` themselves, adding lots of friction to any new network configuration features (which is why I'm writing this..)

This project doesn't do much.  It simply interprets the new directives and interpolates any included files into the fully-rendered output buffer.

