# Composer NPM bridge

*NPM integration for Composer packages.*

## Installation

* Add 'eloquent/composer-npm-bridge' to the project's `composer.json`
  dependencies
* Run `composer install`

## What does the Composer NPM bridge do?

The Composer NPM bridge allows installation and updating of NPM modules via the
Composer command line interface. This allows Composer packages to bring in NPM
dependencies in a similar fashion to regular Composer dependencies.

The bridge uses NPM's [shrinkwrap](https://npmjs.org/doc/shrinkwrap.html)
system to achieve similar functionality to Composer's
[lock files](http://getcomposer.org/doc/01-basic-usage.md#composer-lock-the-lock-file).

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

## Code quality

Composer NPM bridge strives to attain a high level of quality. A full test suite
is available, and code coverage is closely monitored.

### Latest revision test suite results
[![Build Status](https://secure.travis-ci.org/eloquent/composer-npm-bridge.png)](http://travis-ci.org/eloquent/composer-npm-bridge)

### Latest revision test suite coverage
<http://ci.ezzatron.com/report/composer-npm-bridge/coverage/>
