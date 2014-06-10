# Forums

All classes in this folder are simple configuration classes. We want to be able to translate the titles and descriptions
of the forums with our Intl/gettext based system and don't want to store them in the database. This has several
advantages. First of we can directly access the translations by simply instantiating the object, no database look-ups.
Secondly we can reuse existing translations and even combine some. All of this wouldn't be possible if we'd store that
information in the database. It also makes sure that only developers with access to the repository are able to create
new forums, plus we get the git history for free along with it.

## Console Command

A console command exists to help you creating a new forum, simply execute `movadmin create-forum` and follow the on-
screen instructions (or read the help and supply all arguments at once).
