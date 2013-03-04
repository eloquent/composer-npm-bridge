# Composer NPM bridge

*NPM integration for Composer packages.*

[![Build Status]](http://travis-ci.org/eloquent/composer-npm-bridge)
[![Test Coverage]](http://eloquent-software.com/composer-npm-bridge/artifacts/tests/coverage/)

## Installation

Available as [Composer](http://getcomposer.org/) package
[eloquent/composer-npm-bridge](https://packagist.org/packages/eloquent/composer-npm-bridge).

## What does the Composer NPM bridge do?

The Composer NPM bridge allows installation and updating of NPM modules via the
Composer command line interface. This allows Composer packages to bring in NPM
dependencies in a similar fashion to regular Composer dependencies.

The bridge uses NPM's [shrinkwrap](https://npmjs.org/doc/shrinkwrap.html)
system to achieve similar functionality to Composer's
[lock files](http://getcomposer.org/doc/01-basic-usage.md#composer-lock-the-lock-file).

Currently Composer does not pass events to the handler scripts of dependencies.
This means that this component is, unfortunately, fairly useless in its current
incarnation. In the event that Composer changes the way its scripts work, this
component will be updated and may become more useful.

## Usage

After adding the bridge to the project's Composer dependencies as described in
the [installation](#installation) section, add the following additional settings
to `composer.json`:

```json
{
    "scripts": {
        "post-install-cmd": [
            "Eloquent\\Composer\\NPMBridge\\NPMBridge::handle"
        ],
        "post-update-cmd": [
            "Eloquent\\Composer\\NPMBridge\\NPMBridge::handle"
        ]
    }
}
```

Now, assuming that the NPM `package.json` is set up as required, NPM
dependencies will be installed and updated alongside the project's Composer
dependencies.

The install/update process will generate an `npm-shrinkwrap.json` file which
is similar in purpose to a Composer lock file. This file should be added to the
source code management repository (Git/Subversion/Mercurial etc.)

<!-- references -->
[Build Status]: https://raw.github.com/eloquent/composer-npm-bridge/gh-pages/artifacts/images/icecave/regular/build-status.png
[Test Coverage]: https://raw.github.com/eloquent/composer-npm-bridge/gh-pages/artifacts/images/icecave/regular/coverage.png
