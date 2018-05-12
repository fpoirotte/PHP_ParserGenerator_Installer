Compile parsers automatically upon installation
===============================================

This repository contains a custom Composer installer meant for
projects that use parsers written using the `fpoirotte/php_parsergenerator`_
Composer package.

When a package relying on this installer is installed or updated,
its parsers will automatically be rebuilt if necessary, based on
their source grammar. Likewise, those parsers are automatically
erased when the package is uninstalled.

Usage
-----

To use this installer, edit your project's ``composer.json`` file:

* Set the type to ``php-parsers``
* Add a requirement on ``fpoirotte/php_parsergenerator_installer``
* Declare your parsers using the ``php-parsers`` extra option

The extra option may contain either:

* A list of relative paths to grammars: for each such grammar, a parser
  will be generated in the same folder with the same base name
  (eg. ``src/Foo.y`` gets compiled into ``src/Foo.php``)
* A mapping between the expected parsers and their source grammar (see below)

For example, the ``erebot/intl`` Composer package uses the following
configuration to build a parser in ``src/PluralParser.php`` based on
the contents of the grammar located in ``data/PluralParser.y``,
relative to the package's root directory:

..  sourcecode:: json

   {
        "name": "erebot/intl",
        "type": "php-parsers",
        "require": {
            "fpoirotte/php_parsergenerator_installer": "^0.1.0"
        },
        "extra": {
            "php-parsers": {
                "src/PluralParser.php": "data/PluralParser.y"
            }
        }
    }

Now, when installing/updating a package that uses this installer,
you will see output similar to this one:

..  sourcecode:: console

    clicky@localhost:~/git/Erebot/Styling$ composer.phar update
    Loading composer repositories with package information
    Updating dependencies (including require-dev)
    Package operations: 34 installs, 0 updates, 0 removals
      - Installing fpoirotte/php_parsergenerator (0.2.3): Loading from cache
      - Installing fpoirotte/php_parsergenerator_installer (0.1.4): Loading from cache
      ...
    Generating autoload files
    Compiling '.../vendor/erebot/intl/src/PluralParser.php' from '.../vendor/erebot/intl/data/PluralParser.y'
    Parser statistics: 28 terminals, 3 nonterminals, 27 rules
                       55 states, 0 parser table entries, 20 conflicts
    20 parsing conflicts.
    Compiling 'src/Styling/Parser.php' from 'data/Styling.y'
    Parser statistics: 8 terminals, 3 nonterminals, 9 rules
                       17 states, 0 parser table entries, 0 conflicts


Copyright and license
---------------------

Copyright (c) 2018, François Poirotte.
This installer is licensed under the 3-clause BSD License, see the `LICENSE file`_
for more information.

..  _`fpoirotte/php_parsergenerator`: https://packagist.org/packages/fpoirotte/php_parsergenerator
..  _`Erebot/intl`: https://packagist.org/packages/erebot/intl
..  _`LICENSE file`: https://github.com/fpoirotte/PHP_ParserGenerator_Installer/blob/master/LICENSE

..  : vim: ts=4 et
