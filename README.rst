Weblinguist
===========

Overview
--------
Weblinguist is a small and simple web tool to aid translating Qt applications.
It shows a list of available .ts files and allows editing the strings. Upon
submission, the changes .ts file is sent back to the user so he can save and
submit it to the project he's translating the .ts files of.


Requirements
------------
Webserver with
- PHP (tested on 5.3)
- GD module (optional)


Installation
------------
Put the files somewhere on your web server. Add the .ts files in a subdirectory
called `lang/`. If you want information about the file details, also put an
information file using the same filename as the .ts file with the additional
extension `.info` along the `.ts` file containing the output of `svn info`.


License
-------
Weblinguist is licensed under the terms of the GPL. See the file COPYING.
Icons are taken from the Tango project (http://tango.freedesktop.org), licensed
as Public Domain.

