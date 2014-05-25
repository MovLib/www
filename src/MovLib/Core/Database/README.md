# MovLib Core Database

The database abstraction layer, as always highly inspired by [Drupal](https://drupal.org/). The main purpose of these
classes is unification of all database queries and type hinting. First we didn't want to abstract things to much because
it hurts performance. But there are so many things one would have to check while building a query, that an abstraction
is simply necessary. Plus it allows for much better design by restricting the objects via type hinting that are passed
around. If there's one thing I've learned so far, it's "don't trust anyone to do the right thing (that includes
yourself)". It's best to be as strict in your design as humanly possible.

## Weblinks

* https://github.com/drupal/drupal/tree/8.x/core/lib/Drupal/Core/Database
