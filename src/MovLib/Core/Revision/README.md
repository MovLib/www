# Revision

Contains the base implementation to turn any object into a revisioned object. This isn't only for our entity's, it works
for e.g. system pages as well.

## How to implement?

You have to create two classes:

* `\MovLib\Data\Name\Name`
* `\MovLib\Data\Name\NameRevision`

Your first class is the language dependend originator that is used for presentation purposes and editing. The second
class is the memento class that will take care of representing revisions. Your originator has to implement the
`\MovLib\Core\Revision\OriginatorInterface`. To ease implementation it's recommended to use the
`\MovLib\Core\Revision\OriginatorTrait` which implements all the heavy stuff and allows you to hook into the process at
various stages. You'll have to implement at least two methods after you implemented the interface and used the trait in
your class:

* `doCreateRevision()`
  * This method is called if a new revision should be created from the current state of your class. The revision itself
    was already instantiated by the trait and is passed to this hook. Your job is to export everything from your current
    state into this revision, including deletions. You have the `setRevisionArrayValue()` method at your disposal that
    helps you to export dynamic column values, especially the language dependend ones.
* `doSetRevision()`
  * This method is called if an old revision should be recreated. The default properties from the old revision were
    already exported by the trait into the class scope. Your job is it to export the custom properties. Pretty much the
    reverse of what you did in the other method. You have the `getRevisionArrayValue()` method at your disposal that
    helps you with exporting dynamic column values, especially the language dependend ones.

Your second class (the memento class with the *Revision* suffix) has to extend the `\MovLib\Core\Revision\AbstractRevision`
class. You'll have to implement at least two methods:

* `addCreateFields()`
  * This method is called after the insert object has been prepared with the default values and before it will be
    executed. You have to add all custom fields to the statement. If you have custom fields that belong into another
    table read on.
* `addCommitFields()`
  * Same as above but for commits. Note that the word commit refers to the user commiting and not the transaction.

You have four hooks in total that are designed to perform additional work, like inserting data into other tables, in
both your classes, the originator and the memento (the methods are the same):

* `preCreate` and `preCommit`
  * These hooks are called before the new revision is either created (only happens once, the initial commit) or edited
    and commited. You should perform updates, deletes and inserts on other tables of your custom data in this hook.
    You'll effectively abort the complete transaction if something goes wrong as early as possible. You can abort the
    transaction in the post hooks as well by throwing any kind of exception, but a lot more processing was done at those
    points that you'll rollback.
* `postCreate` and `postCommit`
  * Same situation as above, but the revision was already inserted. This hook should be used to perform actions that are
    not directly related to the revision but are needed. A good example is the search indexing, which happens in the
    post hooks. Have a look at `\MovLib\Core\Search\RevisionTrait`.
