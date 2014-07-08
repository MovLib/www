# Coding Standards

**Coding Standards** are an integral part of any software project. Our coding standards are version-independent and
alive. We adopt to new language features and might change things from time to time. The only thing that matters is that
all developers agree on one standard. The coding standards are based on the
[Drupal Coding Standards](https://www.drupal.org/coding-standards) and the
[PSR-2 Coding Style Guide](http://www.php-fig.org/psr/psr-2/).

The key words **must**, **must not**, **required**, **shall**, **shall not**, **should**, **should not**,
**recommended**, **may** and **optional** in this document are to be interpreted as described in
[RFC 2119](http://www.ietf.org/rfc/rfc2119.txt).

## Style Guide

The style guide applies to all languages in use and only if the language supports the feature described.

### Indenting and Whitespace

#### Code

* Code **must** be indented with 2 spaces, not tabs.

#### Lines

* Lines **must not** have a hard limit.
* Lines **must** have a soft limit of 120 characters.
* Lines **should not** be longer than 80 characters.
* Lines **must not** have trailing whitespace at the end.
* Blank lines **may** be added to improve readability and to indicate related blocks of code.
* Lines **should not** contain more than one statement.

#### Files

* Files **must** be encoded in UTF-8 without BOM.
* Files **must** be formatted with `\n` line endings (Linux).
* Files **must** end in a single empty newline (`\n`).

### Operators

* All binary operators (operators that come between two values), such as `+`, `-`, `=`, `!=`, `==`, `!==`, `===`, `>`,
  etc. **must** have a space before and after the operator.
* Unary operators (operators that operate on only one value), such as `++` or `--`, **must not** have a space between
  the operator and the variable they operate on.

### Casting

* The type **must** be enclosed in brackets.
* The type **must not** include any whitespace, e.g. `(integer)` not `( integer )`.
* The type **must** be the long, readable version, e.g. `integer` not `int`.
* The cast **must** be separated with a single space from the variable, e.g. `(integer) $var` not `(integer)$var`.

### Control Structures

* There **must** be one space after the control structure keyword.
* There **must not** be a space after the opening bracket.
* There **must not** be a space before the closing bracket.
* There **must** be one space between the closing bracket and opening brace.
* The structure body **must** be indented once.
* The closing brace **must** be on the next line after the body with the same indent as the control structure.

#### `if`, `else`

    <?php

    if ($expr1) {
      // if body
    }
    elseif ($expr2) {
      // elseif body
    }
    else ($expr3) {
      // else body
    }

* The keyword `elseif` **must** be used instead of `else if` if the language supports it.

#### `switch`

    <?php

    switch ($expr) {
      case 0:
        echo "First case, with a break";
        break;

      case 1:
        echo "Second case, which falls through";
        // fall-through

      case 2:
      case 3:
      case 4:
        echo "Third case, return instead of break";
        return;

      default:
        echo "Default case";
    }

* Each `case` **must** be indented once from `switch`.
* Each `case` body and terminating keyword **must** be indented once from `case`.
* Each `case`'s terminating keyword **must** be followed by one blank line for readability.
* There **must** be a comment such as `// fall-through` when fall-through is intentional in a non-empty `case` body.
* The `default` **must** be last.
* The `default` **should not** be terminated by keyword.
