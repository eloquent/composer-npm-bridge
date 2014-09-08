# Composer NPM bridge

*NPM integration for Composer packages.*

[![The most recent stable version is 2.1.1][version-image]][Semantic versioning]
[![Current build status image][build-image]][Current build status]
[![Current coverage status image][coverage-image]][Current coverage status]

## Installation and documentation

* Available as [Composer] package [eloquent/composer-npm-bridge].
* [API documentation] available.

## Requirements

* The `npm` executable must be available in PATH

## Usage

To utilize the *Composer NPM bridge*, simply add `eloquent/composer-npm-bridge`
to the `require` section of the project's Composer configuration:

    composer require eloquent/composer-npm-bridge:~2

NPM dependencies are specified via a [package.json] configuration file in the
root directory of the Composer package. Source control should be configured to
ignore NPM's `node_modules` directory, similar to Composer's `vendor` directory.

## How does it work?

The *Composer NPM bridge* is a Composer plugin that automatically installs and
updates [NPM] packages whenever the corresponding Composer command is executed.
To detect compatible packages, the bridge inspects Composer package
configuration information to find packages that directly require the
`eloquent/composer-npm-bridge` Composer package itself.

In addition to normal operation, `composer install` will [install] NPM
dependencies for all Composer packages using the bridge. This includes the root
package, as well as Composer dependencies. Similarly, `composer update` will
[install] NPM dependencies for all Composer dependencies using the bridge. It
will also [update] and [shrinkwrap] the NPM dependencies for the root project.

NPM dependencies will be installed exactly as if `npm install` were run from the
root directory of the package. This applies even if the package is installed as
a dependency.

## Caveats

Because NPM dependencies are installed underneath the root directory of the
Composer package, Composer may complain about working copy changes when the
package is installed as a dependency. Source control should be configured to
ignore the `node_modules` directory in order to avoid this.

<!-- References -->

[install]: https://npmjs.org/doc/install.html
[NPM]: https://npmjs.org/
[package.json]: https://npmjs.org/doc/json.html
[shrinkwrap]: https://npmjs.org/doc/shrinkwrap.html
[update]: https://npmjs.org/doc/update.html

[API documentation]: http://lqnt.co/composer-npm-bridge/artifacts/documentation/api/
[Composer]: http://getcomposer.org/
[build-image]: http://img.shields.io/travis/eloquent/composer-npm-bridge/develop.svg "Current build status for the develop branch"
[Current build status]: https://travis-ci.org/eloquent/composer-npm-bridge
[coverage-image]: http://img.shields.io/coveralls/eloquent/composer-npm-bridge/develop.svg "Current test coverage for the develop branch"
[Current coverage status]: https://coveralls.io/r/eloquent/composer-npm-bridge
[eloquent/composer-npm-bridge]: https://packagist.org/packages/eloquent/composer-npm-bridge
[Semantic versioning]: http://semver.org/
[version-image]: http://img.shields.io/:semver-2.1.1-brightgreen.svg "This project uses semantic versioning"
