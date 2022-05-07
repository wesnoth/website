wesnoth/website
===============

This repository holds various resources and scripts used by the
**[Battle for Wesnoth][1]** website.

[1]: <https://www.wesnoth.org/>

Most content in this repository is specific to ``wesnoth.org`` and is of little
use to anyone else. If you are looking for the MediaWiki theme used in
``wesnoth.org``, check the **[wesnoth/wesmere][2]** repository instead.

[2]: <https://github.com/wesnoth/wesmere>

Building
===============
To update an existing translation, simply:
* Add the translation's po file to the respective version's `start/<version>/po` directory.
* Run `make`.
* Commit the results.
* Pull the update to the website's VM.
* You're done.
