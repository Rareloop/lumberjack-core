# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 5.0.0

### Changed

- Upgraded the deprecated `zendframework/zend-diactoros` to the new `laminas/laminas-diactoros` package 

## 4.4.0

### Added

- Add session garbage collection

## 4.3.2

### Patched

- Improved log formatting

## 4.3.1

### Patched

- Switched to the Statamic fork of `Stringy` to fully support PHP 7.4

## 4.3.0

### Added

- Support Middleware Aliases on Routes & Controllers
- `Helpers::logger()` (and global `logger()`) helper functions
- Bound the `Logger` instance to the PSR-3 interface `Psr\Log\LoggerInterface` in the Container.

### Patched

- Prevent Errors with a level of `E_USER_NOTICE` or `E_USER_DEPRECATED` from being fatal.

## 4.2.0

### Added

- Macroable support to `QueryBuilder`
- Add `first()` method to `QueryBuilder`
- Allow middleware to be added within a controller, including WordPress controllers
- Add `has()` method to `Config`

### Patched

- Ensure `get()` and `first()` on the `QueryBuilder` return consistent responses

## 4.1.0

### Added

- Macroable support to `Router` and `Post`

## 4.0.0

## 3.3.1 (2018-08-01)

### Patched

- Add Zend Diactoros as a direct package dependency
- Remove unused `use` statements across the codebase

## 3.3.0 (2018-07-17)

### Added

- Add `runningInConsole()` function to `Application`
- Add `mergeConfigFrom()` function to `ServiceProvider`
- Add warning to log when no WP Controller is found

## 3.2.1 (2018-05-27)

### Patched

- Prevent duplicate headers being sent

## 3.2.0 (2018-05-10)

### Added

- Add `Responsable` interface which can be used as a return object in Controllers or added to Exceptions and automatically handled by the application.
- Add `Helpers` class with the following functions `app()`, `config()`, `view()`, `route()` & `redirect()`. These can be added to the global namespace by including the `src/functions.php` file.

## 3.1.0 (2018-03-28)

### Added

- Add support for view models

## 3.0.0 (2018-03-23)
- Initial release. Starting at v3 to keep inline with Lumberjack theme version
