# Composer NPM bridge changelog

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
