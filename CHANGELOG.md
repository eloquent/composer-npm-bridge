# Composer NPM bridge changelog

## 4.1.0 (2018-12-06)

- **[IMPROVED]** The NPM bridge can now be completely disabled by setting the
  `COMPOSER_NPM_BRIDGE_DISABLE` environment variable to a non-empty value
  ([#18], [#19] - thanks [@driskell]).
- **[IMPROVED]** Custom timeouts can now be set on a per-package basis by
  setting the `extra.npm-bridge.timeout` option in `composer.json`
  ([#13], [#19] - thanks [@driskell]).
- **[IMPROVED]** Packages can now choose whether to allow the `npm` executable
  to be absent at install  time by setting the `extra.npm-bridge.optional`
  option in `composer.json` ([#19] - thanks [@driskell]).

[#13]: https://github.com/eloquent/composer-npm-bridge/issues/13
[#18]: https://github.com/eloquent/composer-npm-bridge/issues/18
[#19]: https://github.com/eloquent/composer-npm-bridge/pull/19
[@driskell]: https://github.com/driskell

## 4.0.1 (2017-09-19)

- **[FIXED]** Fixed "class not found" errors when the plugin is removed by
  Composer ([#5], [#17] - thanks [@garex]).

[#5]: https://github.com/eloquent/composer-npm-bridge/issues/5
[#17]: https://github.com/eloquent/composer-npm-bridge/pull/17
[@garex]: https://github.com/garex

## 4.0.0 (2017-07-12)

- **[BC BREAK]** With the introduction of NPM's [`package-lock.json`], *Composer
  NPM bridge* no longer manages shrinkwrap files.
- **[BC BREAK]** Dropped support for PHP 5.

[`package-lock.json`]: https://docs.npmjs.com/files/package-lock.json

## 3.0.1 (2016-02-22)

- **[FIXED]** Fixed bug where Isolator was unable to be autoloaded ([#11]).

[#11]: https://github.com/eloquent/composer-npm-bridge/issues/11

## 3.0.0 (2016-02-12)

- **[BC BREAK]** Stripped down implementation, many public methods removed.
- **[FIXED]** Updated Composer API version constraint ([#10]).

[#10]: https://github.com/eloquent/composer-npm-bridge/issues/10

## 2.1.1 (2014-09-08)

- **[IMPROVED]** Support for custom installation paths as determined by Composer
  plugins.

## 2.1.0 (2014-02-12)

- **[IMPROVED]** Composer dev/production modes are now honored when installing
  NPM dependencies.
- **[IMPROVED]** Can now be utilized as a dev-only dependency of the root
  package.

## 2.0.0 (2014-01-29)

- **[BC BREAK]** Completely re-written as a Composer plugin.
- **[IMPROVED]** Functions without custom Composer script configuration.

## 1.0.1 (2013-03-04)

- **[NEW]** [Archer] integration.
- **[NEW]** Implemented changelog.

[archer]: https://github.com/IcecaveStudios/archer
